# Laravel Massive uploader

Este es un paquete simple de laravel que agrega la funcionalidad de carga masiva en el proyecto con la finalidad de almacenar datos a una o más entidades a la vez sin necesidad de programar un servicio personalizado para este.

## Instalación

### Integración local

1. Clonar el repositorio dentro de su proyecto en la siguiente ruta `[project]/packages/[package_name]`.

```bash
    git clone https://gitlab.com/infra1.delfosti/package-massive-upload
```

2. Declarar el paquete como dependencia y como repositorio dentro del archivo `composer.json` de su proyecto.

```bash
    {
        "require": {
            "delfosti/massive": "*"
        },
        "repositories": [
            {
                "type": "path",
                "url": "packages/[package_name]"
            }
        ],
    }
```

3. Actualizar las dependencias del proyecto con el comando:

```bash
    composer update
```

4. Por ultimo deberá agregar el provider del paquete dentro del archivo `app.php` de la carpeta `config/`.  

```bash
    'providers' => [
        /*
        * Package Service Providers...
        */
        Delfosti\Massive\MassiveServiceProvider::class,
    ]
``` 

### Integración desde packagist

1. Ejecutar el comando:

```bash
    composer require delfosti/massive
```

2. Agregar el provider del paquete dentro del archivo `app.php` de la carpeta `config/`.  

```bash
    'providers' => [
        /*
        * Package Service Providers...
        */
        Delfosti\Massive\MassiveServiceProvider::class,
    ]
``` 

## Uso

### Archivo de configuración

Para la implemetación del paquete es necesario un archivo de configuración, para 
generar este automaticamente, luego de la instalación deberá ejecutar el comando:

```bash
   php artisan vendor:publish
``` 

Luego de ejecutar el comando tendrá un nuevo archivo dentro de la carpeta `config/` de su proyecto
llamado  `massiveupload.php`, este tendrá una estructura base para guiarlo mejor en la implementación.

### Migraciones

El paquete almacena un historial de todas las veces que se ha ejecutado alguna funcionalidad 
de carga masiva en el sistema, para esto trae un migración que debe ser ejecutada luego de ser instalado. 

```bash
   php artisan migrate
``` 

Luego de ejecutar el comando tendrá una nueva tabla en la base de datos de su proyecto llamada `massive_upload_log`.

### Rutas del API

El paquete agrega nuevas rutas al sistema para poder ejecutar sus funcionalidades, estos son los siguientes:

```bash
   (GET) /api/massive-upload/get-actions
``` 
* Devuelve todas las funcionalidades que han sido declaradas en el archivo de configuración del proyecto.

```bash
  (GET) /api/massive-upload/get-action
```
* Devuelve una funcionalidad en específico, támbien devuelve los campos que se usan para el almacenamiento de cada entidad 
y cuales son obligatorios, para esto se deben envíar los siguientes parametros:

```bash
    {
        "action": Nombre de la funcionalidad (string),
        "entity_fields": Si requiere o no los campos de cada entidad (boolean)
    }
``` 

```bash
  (POST) /api/massive-upload/uploader
```  
* Ejecuta la funcionalidad de carga masiva, para esto se deben envíar los siguientes parametros:

```bash
    {
        "action": Nombre de la funcionalidad (string),
        "user": id del usuario que está ejecutando la acción (boolean),
        "file_name": Nombre del archivo que se está procesando (string),
        "items": Datos que van a ser procesados (array)
    }
``` 

```bash
   (GET) /api/massive-upload-log/show
```

* Devuelve un registro del historial de carga masiva, esta busqueda si hace por el campo `id`;

```bash
   (GET) /api/massive-upload-log/get
```

* Devuelve un listado de registros del historial de carga masiva;

```bash
   (GET) /api/massive-upload-log/list
```

* Devuelve un listado paginado de registros del historial de carga masiva;


### Configuración del proyecto

```bash
    'application' => [
        'architecture' => '',
        'orchestrator' => '',
        'microservices' => [],
        'created_data' => [
            'table' => '',
            'primary_key' => '',
            'fields'
        ]
    ],
``` 

__Architecture:__ Tipo de arquitectura que sigue el proyecto, en este caso el paquete soporta 2 tipos:

   - monolith
   - microservices 

__Orchestrator:__ Es la aplicación principal del proyecto, si la arquitectura del proyecto es de tipo `monolith` debe colocar la URL de la misma aplicación, de 
lo contrario si el proyecto tiene una arquitectura de tipo `microservices` debe colocar la URL de la aplicación desde donde se gestionan los microservicios.

__Microservices:__ Se deben colocar las URLS de los microservicios que componen el proyecto. Solo es necesario llenarlo si la arquitectura de la aplicación es de tipo
`microservices`, caso contrario se puede dejar esta opción vacía.

    - https://microservice-one.com

_Nota_: Para que el paquete funcione correctamente debe ser instalado en todos los servicios que componen el proyecto y todas las acciones 
deben apuntar al orquestador o aplicación principal.

__Created Data:__ Se debe indicar desde donde el paquete va a obtener los datos del usuario que ha realizado las acciones de carga masiva.

* table: tabla de la base de datos desde donde se obtendrán los datos
* primary_key: clave primaria de la tabla
* fields: datos que del usuario que desea mostrar

### Configuración de funcionalidades

```bash
    'functionalities' => [
        [
            'action' => '',
            'type' => '',
            'friendly_name' => '',
            'entities' => []
        ]
    ]
``` 

__Action:__ Identificador de la acción, este debe ser unico y declarado con notación de tipo `snake_case`.

__Type:__ Tipo de acción que se va a ejecutar, el paquete soporta 3 metodos:

- create
- update
- delete

__Friendly name:__ Nombre amigable para el usuario final.

__Entities:__ Entidades del sistema que serán usadas en esta funcionalidad.

### Configuración de entidades

```bash
    'entities' => [
        'entity' => '',
        'order' => 1,
        'type' => '',
        'has_id' => true,
        'created' => '',
        'search_by' => '',
        'audit_dates' => []
        'foreign_keys' => [
            'in_flow' => [
                'Entity' => 'field',
            ],
            'out_flow' => [
                [
                    'entity' => '',
                    'search_by' => '',
                    'fk_column' => ''
                ]
            ]     
        ],
        'fields' => [],
        'validations' => [
            'create' => [],
            'update' => [],
            'delete' => []
        ]
    ]
``` 

__Entity:__ Entidad en la que se procesarán los datos, esta debe tener el mismo nombre del archivo creado en el proyecto.

__Order:__ Orden en que los datos relacionados a una entidad serán ejecutados.

__Type:__ Esto ayuda a identificar el tipo de entidad que está siendo ejecutada, 
el paquete soporta dos tipos de entidad:

- __parent:__ No depende de ninguna entidad que se esté ejecutando en el flujo de la funcionalidad.
- __child:__ Depente de otras entidades que están declaradas en el flujo de la funcionalidad y que tienen que 
        ser ejecutadas antes que esta para su correcto almacenamiento. 

__Has Id:__ Indica si la entidad tiene el campo 'id', esto se usa para validar si el paquete debe obtener el id del dato registrado o no, este campo es opcional y por defecto tiene el valor `true`.

__Created:__ Indica el campo de la entidad que se usa para almacenar el id del usuario que está ejecutando la acción, 
        este es campo es opcional y por defecto busca la propiedad `user_id` en los campos de la entidad que se está procesando,
        si este no lo tiene simplemente ignora almacenar este campo y continua el flujo.

__Search by:__ Campo de la entidad por el cual buscará el registro en la base de datos, este solo es 
        requerido para las acciones de tipo `update` y `delete`.

__Audit Dates:__ Son los campos de la entidad en donde se almacena la fecha y hora en que estpa siendo almacenado o actualizado, por ejemplo `created_at`.

__Foreign Keys -> In Flow:__ Son los claves foreneas que necesitan ser almacenadas en la entidad hija y que son obtenidas de las entidades padre que están dentro del flujo de la funcionalidad y que han
sido procesadas antes que esta, Este campo es de tipo `array` ya que la entidad puede requerir mas de un valor para se procesada.

```bash
    'foreign_keys' => [
        'in_flow' => [
            'Entity' => 'field'
        ]
    ]

    * Entity: Entidad padre desde donde se obtendrá el id, esta debe estar declarada 
            dentro de la funcionalidad.
    * Field: Campo de la entidad hija que requiere el valor.  
``` 

__Foreign Keys -> Out Flow:__ Son los claves foreneas que necesitan ser almacenadas en la entidad, pero no pertenecen al flujo de
la funcionalidad. Este campo es de tipo `array` ya que la entidad puede requerir mas de un valor para ser procesada.

```bash
    'foreign_keys' => [
        'out_flow' => [
            [
                'entity' => Entidad en la que se buscará el registro requerido,
                'search_by' => Campo de la entidad por el cual buscará el registro en la base de datos,
                'fk_column' => Campo de la entidad que requiere el valor, este ayuda a identificar el 
                        campo con el cual se tiene que intercambiar el valor una vez obtenido el registro
            ]
        ]
    ]
``` 

__Fields:__ Campos de la tabla que estarán habilitados para la carga masiva, estos se usan en caso la funcionalidad requiera un almacenamiento personalizado de la entidad. 
Este es un campo opcional y por defecto el paquete obtendrá los campos que fueron declarados en la configuración del modelo.

__Validations:__ Validaciones del paquete Laravel Validator que serán usadas para tener un almacenamiento
mas estricto de datos, estos se usan en caso la funcionalidad requiera validaciones personalizadas para el almacenamiento de la entidad. 
Este es un campo opcional y por defecto el paquete obtendrá los campos que fueron declarados en la configuración del modelo.


### Configuración de Modelos

Agregar una nueva variable publica en cada modelo que tendrá la opción de realizar carga masiva.

```bash
    public $massiveUpload = [
        'table_name' => '',
        'fields' => [],
        'validations' => [
            'create' => [],
            'update' => [],
            'delete' => []
        ]
    ];
``` 

__Table name:__ Tabla de la base de datos en la que se almacenarán los elementos enviados. 

__Fields:__ Campos de la tabla que estarán habilitados para la carga masiva. 

__Validations:__ Validaciones del paquete Laravel Validator que serán usadas para tener un almacenamiento
mas estricto de datos.
