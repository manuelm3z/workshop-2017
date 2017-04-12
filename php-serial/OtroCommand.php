<?php
	namespace Core\Cli\Commands;

	use Core\Mongo;
	use Core\Cli\Command;
	use Models\RemotasModel;
	use Models\EventosModel;
	use Core\Helper\Dates;
	use Components\WebSocket;

	class OtroCommand extends Command
	{
		public function run($options)
		{
			$development = false;

			if (isset($options['-d']))
			{
				$development = true;
        	}

        	$this->ports = array();

        	$model = new RemotasModel();

        	while (TRUE)
        	{
        		$remotes = $model->find(array(
        			'Estado' => '1'
        		));

        		if (count($remotes) > 0)
        		{
	        		foreach ($remotes as $key => $remote)
	        		{
	            		if (isset($remote->Serial))
	            		{
	                		if (!isset($this->ports[$remote->Serial]))
	            			{
	                			exec("stty -F /dev/" . $remote->Serial . " 4800 cs8 -cstopb parodd");

	                			$this->ports[$remote->Serial] = fopen("/dev/" . $remote->Serial, "w+");
	            			}
	            		}

			            if (isset($remote->IP))
			            {
			                $this->ports[$remote->Serial] = FALSE;

			                echo "Aun no implementado\n";
			            }

	            		if ($this->ports[$remote->Serial] !== FALSE)
	            		{
	                		foreach ($remote->Perifericos as $key => $periferico)
	                		{
	                    		if ($periferico->Estado === '1')
	                    		{
			                        $timeout_s = 0;

									$timeout_ms = 140000;

			                        $timeout_ms = $timeout_ms * ($key + 1);

			                        $byte_uno = 192 + (int)$periferico->Id;

			                        $byte_dos = 0;

			                        if (isset($periferico->Comando))
			                        {
			                        	//Esto significa que la estoy mandando a estar a su estado normal
			                        	if ((int)$periferico->Comando === 115)
			                        	{
			                        		$this->setNormal($remote->_id, $periferico->Id);
			                        	}

			                        	$byte_dos = (int)$periferico->Comando;
			                        }

			                        $msg = chr($byte_uno) . chr($byte_dos);

			                        fwrite($this->ports[$remote->Serial], $msg);

			                        stream_set_timeout($this->ports[$remote->Serial], $timeout_s, $timeout_ms);

			                        usleep($timeout_ms);

			                        $w = NULL;

			                        $e = array($this->ports[$remote->Serial]);
			                        
			                        $stream = array($this->ports[$remote->Serial]);

			                        $ascii_msg = "[{$byte_uno}][{$byte_dos}]";

			                        $num = stream_select($stream, $w, $e, $timeout_s, $timeout_ms);

			                        if ($num === FALSE)
			                        {
			                        	$this->saveEvent(array(
			                        		'remote' => (string)$remote->_id,
			                        		'peripheral' => $periferico->Id,
			                        		'code' => 503,
			                        		'description' => 'Error de comunicación con el dispositivo'
			                        	));

			                            if ($development)
			                            {
			                                echo "Remota: {$remote->_id} - Periferico: {$periferico->Id} - Mensaje Enviado: {$ascii_msg} Respuesta: Error de comunicacion con el dispositivo.\n";
			                            }
			                        }
			                        elseif ($num === 0)
			                        {
			                        	$this->saveEvent(array(
			                        		'remote' => (string)$remote->_id,
			                        		'peripheral' => $periferico->Id,
			                        		'code' => 408,
			                        		'description' => 'Dispositivo excedió tiempo de respuesta',
			                        		'cable' => 'ambos'
			                        	));

			                            if ($development)
			                            {
			                                echo "Remota: {$remote->_id} - Periferico: {$periferico->Id} - Mensaje Enviado: {$ascii_msg} Respuesta: Dispositivo excedió tiempo de respuesta.\n";
			                            }
			                        }
			                        elseif ($num > 0)
			                        {
			                            $error = FALSE;

			                            $id = $periferico->Id;

			                            $response = stream_get_contents($this->ports[$remote->Serial], 2);

			                            if (isset($response[0]))
			                            {
			                                if (ord($response[0]) >= 128)
			                                {
			                                    $id = ord($response[0]) - 128;
			                                }
			                                else
			                                {
			                                    $id = ord($response[0]);
			                                }
			                            }
			                            else
			                            {
			                                $error = TRUE;
			                            }

			                            if (isset($response[1]))
			                            {
			                                $status = ord($response[1]);
			                            }
			                            else
			                            {
			                                $error = TRUE;
			                            }

			                            if ($error)
			                            {
			                            	$this->saveEvent(array(
				                        		'remote' => (string)$remote->_id,
				                        		'peripheral' => $id,
				                        		'code' => 500,
				                        		'description' => 'Error en respuesta de dispositivo'
				                        	));
			                                
			                                if ($development)
			                                {
			                                    echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Error en respuesta de dispositivo.\n";
			                                }
			                            }
			                            else
			                            {
			                                if ($status === 0)
			                                {
			                                    WebSocket::sendMessage(json_encode(array(
			                                        'tipo' => 'event::plate',
			                                        'data' => array(
			                                            'id' => $id,
			                                            'code' => $status,
			                                            'central' => (string)$remote->_id,
			                                            'time' => Dates::fullDate()
			                                        )
			                                    )), "ws://" . $this->config['SOCKET_SERVER']);
			                                    
			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Activo.\n";
			                                    }
			                                }
			                                elseif ($status === 1)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Vibración en cable izquierdo',
					                        		'cable' => 'izquierdo'
					                        	));
			                                    
			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Vibracion cable izquierdo.\n";
			                                    }
			                                }
			                                elseif ($status === 2)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Cable izquierdo cortado',
					                        		'cable' => 'izquierdo'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Cable izquierdo cortado.\n";
			                                    }
			                                }
			                                elseif ($status === 8)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Vibración en cable derecho',
					                        		'cable' => 'derecho'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Vibracion en cable derecho.\n";
			                                    }
			                                }
			                                elseif ($status === 9)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Ambos cables vibrando',
					                        		'cable' => 'ambos'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Ambos cables vibrando.\n";
			                                    }
			                                }
			                                elseif ($status === 10)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Cable izquierdo cortado y cable derecho vibrando',
					                        		'cable' => 'ambos'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Cable izquierdo cortado y cable derecho vibrando.\n";
			                                    }
			                                }
			                                elseif ($status === 16)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Cable derecho cortado',
					                        		'cable' => 'derecho'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Cable derecho cortado.\n";
			                                    }
			                                }
			                                elseif ($status === 17)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Cable izquierdo vibrando y cable derecho cortado',
					                        		'cable' => 'ambos'
					                        	));
			                                    
			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Cable izquierdo vibrando y cable derecho cortado.\n";
			                                    }
			                                }
			                                elseif ($status === 18)
			                                {
			                                	$this->saveEvent(array(
					                        		'remote' => (string)$remote->_id,
					                        		'peripheral' => $id,
					                        		'code' => $status,
					                        		'description' => 'Ambos cables cortados',
					                        		'cable' => 'ambos'
					                        	));

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Ambos cables cortados.\n";
			                                    }
			                                }
			                                elseif ($status === 27)
			                                {
			                                    WebSocket::sendMessage(json_encode(array(
			                                        'tipo' => 'event::plate',
			                                        'data' => array(
			                                            'id' => $id,
			                                            'code' => $status,
			                                            'central' => (string)$remote->_id,
			                                            'time' => Dates::fullDate()
			                                        )
			                                    )), "ws://" . $this->config['SOCKET_SERVER']);

			                                    if ($development)
			                                    {
			                                        echo "Remota: {$remote->_id} - Periferico: {$id} - Mensaje Enviado: {$ascii_msg} Respuesta: Inicializando.\n";
			                                    }
			                                }
			                            }              
			                        }
			                    }
			                }
			            }
			        }
        		}
        		else
        		{
        			if ($development === TRUE)
        			{
        				echo "No se encontraron remotas en base de datos.\n";
        			}
        		}
		    }
		}

		public function saveEvent($options = array())
		{
			$options['time'] = Mongo::StringtoDate(Dates::fullDate());

			WebSocket::sendMessage(json_encode(array(
                'tipo' => 'event::plate',
                'data' => array(
                    'id' => $options['peripheral'],
                    'code' => $options['code'],
                    'central' => (string)$options['remote'],
                    'time' => $options['time']
                )
            )), "ws://" . $this->config['SOCKET_SERVER']);

            $this->saveToDbEvent($options);
		}

		public function getLastEvent($remote, $peripheral)
		{
			$model = new EventosModel();

			$lastEvent = $model->findOne(array(
				'Remota' => $remote,
				'Periferico' => $peripheral
			));

			return $lastEvent;
		}

		public function saveToDbEvent($options = array())
		{
			if (isset($options['code']))
			{
				if (isset($options['remote']) && isset($options['peripheral']))
				{
					$lastEvent = $this->getLastEvent($options['remote'], $options['peripheral']);

					$evento = new EventosModel();

					$evento->Remota = (string)$options['remote'];
					
					$evento->Periferico = (string)$options['peripheral'];
					
					$evento->Codigo = (string)$options['code'];
					
					if (isset($options['description']))
					{
						$evento->Descripcion = (string)$options['description'];
					}

					if (isset($options['time']))
					{
						$evento->Fecha = $options['time'];
					}

					if (isset($options['cable']))
					{
						$evento->Cable = array();

						if ($options['cable'] === 'izquierdo')
						{
							$evento->Cable[] = 'izquierdo';
						}
						elseif ($options['cable'] === 'derecho')
						{
							$evento->Cable[] = 'derecho';
						}
						elseif ($options['cable'] === 'ambos')
						{
							$evento->Cable[] = 'izquierdo';

							$evento->Cable[] = 'derecho';
						}
					}

					if ($lastEvent !== FALSE)
					{
						if ($lastEvent->Codigo !== $options['code'])
						{
							$result = $evento->insert();

							if ($result !== TRUE)
							{
								throw new Exception($result, 1);
							}
							else
							{
								return TRUE;
							}
						}
						else
						{
							$lastEvent = NULL;

							$evento = NULL;
						}
					}
					else
					{
						$result = $evento->insert();

						if ($result !== TRUE)
						{
							throw new \Exception($result, 1);
						}
						else
						{
							return TRUE;
						}
					}
				}
				else
				{
					throw new \Exception("No se puede guardar el evento sin el id de la remota y el dispositivo.", 1);
				}
			}
			else
			{
				throw new \Exception("EL evento no se puede crear sin un codigo.", 1);
			}
		}

		public function setNormal($remote, $peripheral)
		{
			$model = new RemotasModel();

			$remote = $model->findOne(array(
				'_id' => (string)$remote
			));

			foreach ($remote->Perifericos as $key => &$periferico)
			{
				if ($periferico->Id === $peripheral)
				{
					unset($periferico->Comando);
				}
			}

			return $remote->update();
		}
	}
?>