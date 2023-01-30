# Massive uploader - Package

## Uso

### Integración local

- Descargar o clonar el repositorio dentro de la carpeta 

```bash
    [your_project]/packages/[package_name]
```

- Declarar el package en las dependencias y en los repositorios de tu proyecto

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

- Para que el paquete se instale deberá ejecutar el siguiente comando:

```bash
    composer update
```

- Por ultimo deberá agregar el provider del package dentro de "config/app" 

```bash
    /*
    * Package Service Providers...
    */
    Delfosti\Massive\MassiveServiceProvider::class,
``` 

### Integración desde packagist

- Ejecutar el comando:

```bash
    composer require delfosti/massive
```

- Agregar el provider del package dentro de "config/app" 

```bash
    /*
    * Package Service Providers...
    */
    Delfosti\Massive\MassiveServiceProvider::class,
``` 

### Desplegar paquete en packagist

- Crear una cuenta dentro del servicio
- Copiar la URL del repositorio
- Entrar a la sección [Submit](https://packagist.org/packages/submit) y pegar dentro del campo "Repository URL"

![App Screenshot](https://res.cloudinary.com/practicaldev/image/fetch/s--gaRK9jJO--/c_limit%2Cf_auto%2Cfl_progressive%2Cq_auto%2Cw_880/https://dev-to-uploads.s3.amazonaws.com/uploads/articles/fewkscf426ruo7juc0md.png)
