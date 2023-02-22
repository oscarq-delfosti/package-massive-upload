# Massive uploader - Laravel Package

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

### Configuración del proyecto

```bash
    'application' => [
        'architecture' => '',
        'orchestrator' => '',
        'microservices' => []
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
        'search_by' => '',
        'finders' => [
            [
                'entity' => '',
                'search_by' => '',
                'fk_column' => ''
            ]
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

__Search by:__ Campo de la entidad por el cual buscará el registro en la base de datos, este solo es 
        requerido para las acciones de tipo `update` y `delete`.

__Finders:__ Son los claves foreneas que necesitan ser almacenadas en la entidad, pero no pertenecen al flujo de
la funcionalidad. Este campo es de tipo `array` ya que la entidad puede requerir mas de un valor para ser procesada.

#### Configuración de Finders

```bash
    'finders' => [
        [
            'entity' => '',
            'search_by' => '',
            'fk_column' => ''
        ]
    ]
``` 

__Entity:__ Entidad en la que se buscará el registro requerido.

__Search by:__ Campo de la entidad por el cual buscará el registro en la base de datos.

__Fk column:__ Campo de la entidad que requiere el valor, este ayuda a identificar el 
campo con el cual se tiene que intercambiar el valor una vez obtenido el registro.

### Configuración de Modelos

Agregar una nueva variable publica en cada modelo que tendrá la opción de realizar carga masiva.

```bash
    public $massiveUpload = [
        'table_name' => '',
        'fields' => [],
        'foreign_keys' => [],
        'validations' => [
            'create' => [],
            'update' => [],
            'delete' => []
        ]
    ];
``` 

__Table name:__ Tabla de la base de datos en la que se almacenarán los elementos enviados. 

__Fields:__ Campos de la tabla que estarán habilitados para la carga masiva. 

__Foreign keys:__ Claves foraneas que serán usadas para identificar a la entidad de tipo `parent` 
que está declarado en el flujo de una funcionalidad, estas deben ser declaradas de la siguiente forma:

    'Entity' => 'foreign key'

__Validations:__ Validaciones del paquete Laravel Validator que serán usadas para tener un almacenamiento
mas estricto de datos.