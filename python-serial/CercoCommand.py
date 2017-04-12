from dotenv import load_dotenv
from pymongo import MongoClient
from json import JSONEncoder
import os
import time
import serial
import datetime
import websocket

dotenv_path = '../.env'
load_dotenv(dotenv_path)


class CercoCommand(object):
    """docstring for CercoCommand"""
    def __init__(self):
        client = MongoClient('mongodb://' + os.environ.get("DATABASE_HOST") + '/')

        self.db = client[os.environ.get("DATABASE")]

        #self.ws = websocket.create_connection(os.environ.get("WEBSOCKET_SERVER"))

        self.development = True

    def run(self):
        while True:
            remotes = self.db.Remotas.find()

            connection = {
                'port': '/dev/ttyS0',
                'type': 'serial'
            }

            for remote in remotes:
                try:
                    connection['port'] = '/dev/' + remote['Serial']
                except Exception as e:
                    try:
                        connection['port'] = remote['Ip']
                        connection['type'] = 'ip'
                    except Exception as e:
                        raise e

                timeout = len(remote['Perifericos']) * 0.5

                ser = serial.Serial(
                    port=connection['port'],
                    baudrate=4800,
                    parity=serial.PARITY_ODD,
                    stopbits=serial.STOPBITS_ONE,
                    bytesize=serial.EIGHTBITS,
                    timeout=timeout
                )

                for peripheral in remote['Perifericos']:
                    if connection['type'] == 'serial':
                        # timeout = (1 + int(peripheral['Id'])) * 0.5

                        # timeout = int(timeout * 10**1) / 10.0**1

                        byte_uno = 192 + int(peripheral['Id'])

                        byte_dos = 0

                        try:
                            byte_dos = int(peripheral['Comando'])
                            if (115 == byte_dos):
                                self.setNormal(remote['_id'], peripheral['Id'])
                        except Exception as e:
                            if self.development is True:
                                print('Sin comando')

                        msg = bytes([byte_uno, byte_dos])

                        if ser.isOpen():
                            ser.write(msg)

                            ser.flush()

                            response = ser.read(2)

                            idRemota = int.from_bytes(response[:1], byteorder='big')
                            
                            status = int.from_bytes(response[1:2], byteorder='big')

                            if idRemota <= 0:
                                self.saveEvent({
                                    "remote": remote['_id'],
                                    "peripheral": peripheral['Id'],
                                    "code": 408,
                                    "description": 'Dispositivo excedió tiempo de respuesta',
                                    "cable": 'ambos'
                                });

                                if self.development is True:
                                    print("Remota:", remote['_id'], "- Periferico:", peripheral['Id'], "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Dispositivo excedió tiempo de respuesta.", sep=" ")
                            else:
                                idRemota = idRemota - 128

                                if status == 0:
                                    # self.ws.send(JSONEncoder().encode({
                                    #     "tipo": 'event::plate',
                                    #     "data": {
                                    #         "id": idRemota,
                                    #         "code": status,
                                    #         "central": str(remote['_id']),
                                    #         "time": str(datetime.datetime.utcnow())
                                    #     }
                                    # }))

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Activo.", sep=" ")

                                elif status == 1:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Vibración en cable izquierdo',
                                        "cable": 'izquierdo'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Vibracion cable izquierdo.", sep=" ")
                                
                                elif status == 2:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Cable izquierdo cortado',
                                        "cable": 'izquierdo'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Cable izquierdo cortado.", sep=" ")

                                elif status == 8:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Vibración en cable derecho',
                                        "cable": 'derecho'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Vibracion en cable derecho.", sep=" ")

                                elif status == 9:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Ambos cables vibrando',
                                        "cable": 'ambos'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Ambos cables vibrando.", sep=" ")

                                elif status == 10:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Cable izquierdo cortado y cable derecho vibrando',
                                        "cable": 'ambos'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Cable izquierdo cortado y cable derecho vibrando.", sep=" ")

                                elif status == 16:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Cable derecho cortado',
                                        "cable": 'derecho'
                                    })


                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Cable derecho cortado.", sep=" ")

                                elif status == 17:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Cable izquierdo vibrando y cable derecho cortado',
                                        "cable": 'ambos'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Cable izquierdo vibrando y cable derecho cortado.", sep=" ")

                                elif status == 18:
                                    self.saveEvent({
                                        "remote": remote['_id'],
                                        "peripheral": idRemota,
                                        "code": status,
                                        "description": 'Ambos cables cortados',
                                        "cable": 'ambos'
                                    })

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Ambos cables cortados.", sep=" ")

                                elif status == 27:
                                    # self.ws.send(JSONEncoder().encode({
                                    #     "tipo": 'event::plate',
                                    #     "data": {
                                    #         "id": idRemota,
                                    #         "code": status,
                                    #         "central": remote['_id'],
                                    #         "time": datetime.datetime.utcnow()
                                    #     }
                                    # }))

                                    if self.development is True:
                                        print("Remota:", remote['_id'], "- Periferico:", idRemota, "- Mensaje Enviado:", byte_uno, byte_dos, "Respuesta: Inicializando.", sep=" ")
                        time.sleep(timeout)
            ser.close()

    def getLastEvent(self, remote, peripheral):
        return self.db.Eventos.find_one({
            'Remota': remote,
            'Periferico': peripheral
        })

    def saveEvent(self, options):
        options['time'] = datetime.datetime.utcnow()

        # self.ws.send(JSONEncoder().encode({
        #     "tipo": "event::plate",
        #     "data": {
        #         "id": options['peripheral'],
        #         "code": options['code'],
        #         "central": str(options['remote']),
        #         "time": options['time']
        #     }
        # }))

        self.saveToDbEvent(options)

    def saveToDbEvent(self, options):
        lastEvent = self.getLastEvent(options['remote'], options['peripheral'])

        event = {
            'Remota': options['remote'],
            'Periferico': options['peripheral'],
            'Codigo': options['code'],
            'Descripcion': options['description'],
            'Fecha': options['time']
        }

        if options['cable'] == 'ambos':
            event['Cable'] = ['izquierdo', 'derecho']
        else:
            event['Cable'] = [options['cable']]

        if lastEvent is not None:
            if lastEvent['Codigo'] != options['code']:
                self.db.Eventos.insert_one(event)

    def setNormal(self, remote, idPeripheral):
        remote = self.db.Remotas.find({
            "_id": remote
        })

        listPeripheral = []

        for peripheral in remote['Perifericos']:
            if peripheral['Id'] != idPeripheral:
                listPeripheral.append(peripheral)

        remote['Perifericos'] = listPeripheral

        result = self.db.Remotas.update_one({
            "_id": remote
        }, remote)

        if result.modified_count > 0:
            return True
        else:
            return False

x = CercoCommand()
x.run()