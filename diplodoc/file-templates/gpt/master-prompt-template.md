Write php #PHP_VERSION# call example with requirements:

1. Variable $serviceBuilder of type ServiceBuilder is already created outside the scope of this example (no need to
   create it).
2. Add call example for method «#METHOD_NAME#» from variable $serviceBuilder, generate full call chain to class
   «#CLASS_NAME#»
3. Use arguments for method «#CLASS_NAME#» from phpDoc annotation:

```
   #METHOD_PARAMETERS#
```   

4. Surround call «#METHOD_NAME#» with try-catch construction and catch Throwable exception
5. For fields with type DateTime use date format Atom
6. Use all essential fields described in phpdoc annotation for method «#METHOD_NAME#»
7. Process return result with related method from class «#METHOD_RETURN_RESULT_TYPE#» and use print function
8. In generation result return only php code without markdown wrapper


Root Entry point service builder methods by scope with related service builders:

```
#ROOT_SERVICE_BUILDER_METHODS#
```

Target Service builder with methods returned related services

```
#SCOPE_SERVICE_BUILDER_METHODS#
```

Return result classes source code
```php
#RETURN_RESULT_CLASS_SOURCE_CODE#

#RETURN_RESULT_SUBORDINATE_CLASSES_SOURCE_CODE#
````

Current service with methods
```php
#CLASS_SOURCE_CODE#
```
