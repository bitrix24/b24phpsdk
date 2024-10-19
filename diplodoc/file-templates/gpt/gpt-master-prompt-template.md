Write php #PHP_VERSION# call example with requirements: 

1. Variable $serviceBuilder of type ServiceBuilder is already created outside the scope of this example (no need to create it). 
2. Add call example for method «#METHOD_NAME#» from variable $serviceBuilder, generate full call chain to class «#CLASS_NAME#»
3. Use arguments for method «#CLASS_NAME#» from phpDoc annotation:

   «#METHOD_PARAMETERS#»

4. Surround call «#METHOD_NAME#» with try-catch construction and catch Throwable exception
5. For fields with type DateTime use date format Atom
6. Use all essential fields described in phpdoc annotation for method «#METHOD_NAME#»

Root Entry point service builder methods by scope with related service builders:

#ROOT_SERVICE_BUILDER_METHODS#

#SCOPE_SERVICE_BUILDER_METHODS#
```php
#CLASS_SOURCE_CODE#
```
