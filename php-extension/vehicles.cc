#include "php_vehicles.h"

PHP_MINIT_FUNCTION(vehicles)
{
	return SUCCESS;
}

zend_module_entry vehicles_module_entry = {
	#if ZEND_MODULE_API_NO >= 20010901
		STANDARD_MODULE_HEADER,
	#endif
		PHP_VEHICLES_EXTNAME,
		NULL, /* Functions */
		PHP_MINIT(vehicles),
		NULL, /* MSHUTDOWN */
		NULL, /* RINIT */
		NULL, /* RSHUTDOWN */
		NULL, /* MINFO */
	#if ZEND_MODULE_API_NO >= 20010901
		PHP_VEHICLES_EXTVER,
	#endif
		STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_VEHICLES
	extern "C" {
		ZEND_GET_MODULE(vehicles)
	}
#endif