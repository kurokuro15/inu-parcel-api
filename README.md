# rest-api

### Proyecto de la materia INU-554 INTERFACES WEB CON EL USUARIO.

#### Creado en Vanila PHP.

Se dispone en este repositorio el apartado de **server-side** o **Backend**, desarrollado como se menciona unicamente con _PHP_, creando una **RESTful API**.

## ENDPOINTS

### /auth

```
[GET]: Recibe la petición para recuperar contraseña.

[POST]: Recibe la petición de acceso [Usuario y contraseña]
		devolviendo un token único con expiración o error.
[POST]: Recibe la petición de cambio de contraseña [Respuesta 1 y 2]
		devolviendo un token único con expiración de 5 minutos o error.

[PUT]: Recibe la petición de cambio de contraseña [Usuario, Contraseña y Token]
	   ingresando en la DB la nueva contraseña y devolviendo éxito o error.
```

### /user

```
[GET]: Recibe parámetros opcionales {id | page} retornando una lista de usuarios o un usuario.
	   Requiere el token generado en el endpoint auth para responder

[POST]: Recibe la petición de creación de un usuario. Toma varios parámetros opcionales
		(User->required y User->nonRequired) devuelve el id del usuario creado o error.

[PUT]: Recibe la petición de modificación de un usuario. Toma los mismos parámetros del [POST] 
	   y devuelve el usuario modificado o error.
	   Esta requiere el token generado en el endpoint auth para responder

[DELETE]: Recibe la petición de eliminación de un usuario. body->{id}
		  Esta requiere el token generado en el endpoint auth para responder
```
### /parcel

```
[GET]: Recibe parámetros opcional {tracking} retornando una lista de encomiendas o una encomienda.
	   Requiere el token generado en el endpoint auth para responder

[POST]: Recibe la petición de creación de una encomienda. Toma varios parámetros 
		(Parcel->required) devuelve el id y estado de la encomienda creada o error.

```
### Para Descargar e Instalar el proyecto:

#### \*CON HTTPS:

```
git clone https://github.com/kurokuro15/api-rest.git
```

#### CON SSH:

```
git clone git@github.com:kurokuro15/api-rest.git
```

#### CON GitHub CLI:

```
gh repo clone kurokuro15/api-rest
```

### Estructura:

Se recomienda desplegar el backend dentro de la carpeta `~/api-rest/` dentro de la raiz. En caso de no poder hacerlo se debe tomar en cuenta los siguientes atributos en el archivo del frontend:

```
~/inu-presupuestos-envios/js/Config.js
```

![Config.js](https://i.ibb.co/8g80jx6/image.png)

Para así evitar problemas de envío y recepción de peticiones.
### Creación de Base de datos:
Se ha de ejecutar las sentencias SQL que se encuentran dentro del archivo `~\GenerateDatabase.sql` ubicado en la raiz del proyecto. 

Se ha de establecer la conexión a la DB en el archivo `~\conection\config`
![config](https://i.ibb.co/5nyPRtS/image.png)

Por último. Este proyecto no servirá en gran medida sin la descarga y configuración del [FrontEnd](https://github.com/kurokuro15/inu-presupuestos-envios) diseñado para este proyecto en _**JS** vanila_.
