<div align="center">

# MANUAL DE USUARIO

## ANÁLISIS Y DESARROLLO DE SOFTWARE

---

## ACCIONES DE SEGTRACK

<br/>
<br/>

**TUTOR**

Elsa María Junca Bernal

<br/>
<br/>

**APRENDICES**

Andres Camilo Carrillo Jaimes

Michael David Montoya Pasachoa

Anderson Estiven Moreno Pinzon

<br/>
<br/>

**2025**

</div>

---

# Proyecto SEGTRACK

## Tabla de Contenido

- [1. Introducción](#1-introducción)
- [2. Descripción del Proyecto](#2-descripción-del-proyecto)
- [3. Quiénes Usan Segtrack](#3-quiénes-usan-segtrack)
  - [3.1 Personal de Seguridad](#31-personal-de-seguridad--los-ojos-en-la-puerta)
- [4. Tecnologías Utilizadas](#4-tecnologías-utilizadas)
- [5. Uso del Sistema](#5-uso-del-sistema)
- [6. Módulo Personal de Seguridad](#6-módulo-personal-de-seguridad)
  - [6.1 Pantalla Principal](#61-pantalla-principal)
  - [6.2 Personal de Seguridad](#62-personal-de-seguridad)
    - [6.2.1 Validaciones del Formulario](#621-validaciones-del-formulario)
  - [6.3 Recuperación de Contraseña](#63-recuperación-de-contraseña)
  - [6.4 Registrar Funcionarios](#64-registrar-funcionarios)
    - [6.4.1 Validaciones del Formulario de Registro](#641-validaciones-del-formulario-de-registro)
  - [6.5 Lista de Funcionarios](#65-lista-de-funcionarios)
    - [6.5.1 Acciones Disponibles](#651-acciones-disponibles-lista-funcionario)
  - [6.6 Registro de Visitante](#66-registro-de-visitante)
  - [6.7 Registro de Dispositivos](#67-registro-de-dispositivos)
  - [6.8 Lista de Dispositivos](#68-lista-de-dispositivos)
  - [6.9 Módulo Vehículos](#69-módulo-vehículos)
  - [6.10 Módulo Instituto](#610-módulo-instituto)
  - [6.11 Módulo Sede](#611-módulo-sede)
  - [6.12 Módulo Lectores de QR](#612-módulo-lectores-de-qr)
- [7. Autores](#7-autores)

---

## 1. Introducción

Este manual ha sido creado para guiar de manera clara y práctica el uso del sistema Segtrack. Su objetivo es que los usuarios puedan comprender fácilmente cómo funciona cada módulo y realizar sus tareas de forma correcta y segura.

Se busca que cualquier persona pueda interactuar con Segtrack, entendiendo los procesos y características principales del sistema de manera organizada y directa.

---

## 2. Descripción del Proyecto

**¿Qué es Segtrack?**

Segtrack es un sistema inteligente de control de acceso para instituciones que necesitan registrar con exactitud quién entra, quién sale, qué dispositivos llevan y en qué parqueadero se encuentran. Todo esto se gestiona mediante **códigos QR**, evitando papeleo, errores humanos y registros manuales.

> 💡 Piensa en Segtrack como un portero digital que nunca se equivoca y siempre registra todo automáticamente.

---

## 3. Quiénes Usan Segtrack

### 3.1 Personal de Seguridad — Los Ojos en la Puerta

🔵 Son quienes están en los puntos de acceso. Su función es escanear el QR de los funcionarios y registrar si traen un dispositivo o ingresan vehículos. Todo debe quedar registrado en tiempo real, con precisión y sin errores.

---

## 4. Tecnologías Utilizadas

| Tecnología | Uso |
|------------|-----|
| ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white) | Lógica del servidor y controladores |
| ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black) | Interacción dinámica en el navegador |
| ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white) | Base de datos relacional |
| ![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat&logo=bootstrap&logoColor=white) | Diseño responsivo de la interfaz |
| ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat&logo=jquery&logoColor=white) | Manipulación del DOM y peticiones AJAX |

---

## 5. Uso del Sistema

El sistema redirige al panel de control según el rol del usuario:

| Rol | Panel de acceso |
|-----|----------------|
| 🔵 Personal de Seguridad | Dashboard de escaneo y registro |

---

## 6. Módulo Personal de Seguridad

### 6.1 Pantalla Principal

La pantalla principal de Segtrack es moderna y clara, mostrando el control de entrada y salida de funcionarios con códigos QR. Desde aquí, el personal puede navegar a todas las funciones que necesita para su rol.

<img src="Public/img/Fotos_Manual/image-5.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-3.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-2.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-4.png" width="1000"/>

---

### 6.2 Personal de Seguridad

Para ingresar, el usuario debe hacer clic en **"Iniciar Sesión"** en la parte superior de la página.

<img src="Public/img/Fotos_Manual/image-5.png" width="1000"/>

Luego se abre el formulario donde se ingresan las credenciales registradas.

<img src="Public/img/Fotos_Manual/image-6.png" width="1000"/>

El sistema validará los datos y los campos del formulario, ya que es una prioridad de validación del sistema.

---

#### 6.2.1 Validaciones del Formulario

- **Campos incompletos:**

<img src="Public/img/Fotos_Manual/image-8.png" width="1000"/>

- **Correo no registrado:**

<img src="Public/img/Fotos_Manual/image-11.png" width="1000"/>

- **Contraseña incorrecta:**

<img src="Public/img/Fotos_Manual/image-12.png" width="1000"/>

- **Credenciales correctas:**

<img src="Public/img/Fotos_Manual/image-13.png" width="1000"/>

---

### 6.3 Recuperación de Contraseña

También contamos con un panel de recuperación de contraseña con los siguientes pasos.

<img src="Public/img/Fotos_Manual/image-14.png" width="1000"/>

Ingresamos el correo personal del funcionario con el cual está registrado.

<img src="Public/img/Fotos_Manual/image-15.png" width="1000"/>

Después el sistema verifica si existe el funcionario y envía el Token.

<img src="Public/img/Fotos_Manual/image-16.png" width="1000"/>

Después debería llegar el token al correo registrado para validar el cambio de contraseña.

<img src="Public/img/Fotos_Manual/not.jpeg" width="1000"/>

Una vez digitado el token y la contraseña nueva según su preferencia, podrá ingresar al login desde la tarjeta de verificación exitosa.

<img src="Public/img/Fotos_Manual/image-17.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-37.png" width="1000"/>

---

Después de registrar su correo y su nueva contraseña, será direccionado a la vista según su cargo.

<img src="Public/img/Fotos_Manual/image-38.png" width="1000"/>

---

### 6.4 Registrar Funcionarios

El sistema registra a los funcionarios para tener un control de entrada y salida de personal por QR.

<img src="Public/img/Fotos_Manual/image-19.png" width="1000"/>

#### 6.4.1 Validaciones del Formulario de Registro

- **Datos duplicados o vacíos:**

<img src="Public/img/Fotos_Manual/image-21.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-40.png" width="1000"/>

- **Formulario completado:**

<img src="Public/img/Fotos_Manual/image-39.png" width="1000"/>

- **Registro exitoso:**

<img src="Public/img/Fotos_Manual/image-20.png" width="1000"/>

---

### 6.5 Lista de Funcionarios

Nos permite verificar todos los funcionarios registrados por el personal de seguridad, validando su estado de activo o inactivo en el instituto.

<img src="Public/img/Fotos_Manual/image-24.png" width="1000"/>
<img src="Public/img/Fotos_Manual/image-26.png" width="1000"/>

#### 6.5.1 Acciones Disponibles lista funcionario

**Vista QR**

<img src="Public/img/Fotos_Manual/image-27.png" width="1000"/>

**Envío QR**

<img src="Public/img/Fotos_Manual/image-28.png" width="1000"/>

**Editar Funcionario** — una vez editado, el QR se envía automáticamente actualizado al funcionario.

<img src="Public/img/Fotos_Manual/image-29.png" width="1000"/>

---

### 6.6 Registro de Visitante

Si es un visitante, se registra y obtendrá un QR de visitante temporal.

<img src="Public/img/Fotos_Manual/image-57.png" width="1000"/>

Ejemplo formulario registrado:

<img src="Public/img/Fotos_Manual/image-58.png" width="1000"/>

Lista de visitantes:

<img src="Public/img/Fotos_Manual/image-59.png" width="1000"/>

---

### 6.7 Registro de Dispositivos

En este módulo se podrán registrar los dispositivos que porta un funcionario o visitante en la institución.

<img src="Public/img/Fotos_Manual/image-30.png" width="1000"/>

Registrar otro dispositivo:

<img src="Public/img/Fotos_Manual/image-31.png" width="1000"/>

Tipo de funcionario al cual se le registra el dispositivo:

<img src="Public/img/Fotos_Manual/image-33.png" width="1000"/>

Verificación en tiempo real del registro del dispositivo:

<img src="Public/img/Fotos_Manual/image-34.png" width="1000"/>

Ejemplo de registro de dispositivo:

<img src="Public/img/Fotos_Manual/image-41.png" width="1000"/>

Verificación de registro:

<img src="Public/img/Fotos_Manual/image-42.png" width="1000"/>

Esta vista cuenta con una opción para ver dispositivos registrados:

<img src="Public/img/Fotos_Manual/image-35.png" width="1000"/>

---

### 6.8 Lista de Dispositivos

Control de envío y visualización del QR de los dispositivos registrados.

<img src="Public/img/Fotos_Manual/image-43.png" width="1000"/>

La cual nos permite visualizar las siguientes acciones:

**Filtrar por datos específicos:**

<img src="Public/img/Fotos_Manual/image-44.png" width="1000"/>

**Ver QR:**

<img src="Public/img/Fotos_Manual/image-45.png" width="1000"/>

**Enviar QR:**

<img src="Public/img/Fotos_Manual/image-46.png" width="1000"/>

**Editar Dispositivo:**

<img src="Public/img/Fotos_Manual/image-47.png" width="1000"/>

---

### 6.9 Módulo Vehículos

En este módulo el personal de seguridad podrá registrar un vehículo del funcionario si este aplica.

<img src="Public/img/Fotos_Manual/image-48.png" width="1000"/>

El formulario está adaptado para registrar los medios de transporte de los funcionarios o visitantes que ingresen a la institución.

<img src="Public/img/Fotos_Manual/image-49.png" width="1000"/>

Ejemplo de registro:

<img src="Public/img/Fotos_Manual/image-50.png" width="1000"/>

Lista de vehículos:

<img src="Public/img/Fotos_Manual/image-51.png" width="1000"/>

La cual nos permite visualizar las siguientes acciones:

**Ver QR:**

<img src="Public/img/Fotos_Manual/image-52.png" width="1000"/>

**Enviar QR:**

<img src="Public/img/Fotos_Manual/image-53.png" width="1000"/>

**Editar Vehículo:**

<img src="Public/img/Fotos_Manual/image-54.png" width="1000"/>

---

### 6.10 Módulo Instituto

Lista de institutos, la cual es solo de visualización de sedes activas o inactivas.

<img src="Public/img/Fotos_Manual/image-56.png" width="1000"/>

---

### 6.11 Módulo Sede

Lista de sedes en las cuales se pueden evidenciar sedes activas o inactivas.

<img src="Public/img/Fotos_Manual/image-55.png" width="1000"/>

---

### 6.12 Módulo Lectores de QR

Con los siguientes lectores se puede escanear cada uno de los funcionarios y dispositivos registrados, permitiendo el control de activo o inactivo en el instituto.

#### Escáner de QR Funcionario

<img src="Public/img/Fotos_Manual/image-60.png" width="1000"/>

1. Identificar si es salida o entrada para registrar el control.

<img src="Public/img/Fotos_Manual/image-61.png" width="1000"/>

2. Tener a la mano el QR de registro.

Ejemplo QR de Luisa:

<img src="Public/img/Fotos_Manual/image-62.png" width="1000"/>

3. Escanear el QR con la cámara.

<img src="Public/img/Fotos_Manual/image-64.png" width="1000"/>

Después de escanear el QR, el sistema mostrará la foto del funcionario para tener un medio de validación visual de seguridad.

<img src="Public/img/Fotos_Manual/image-65.png" width="1000"/>

Por último, este será agregado a una lista de movimientos de funcionarios, ya sea de salida o entrada.

<img src="Public/img/Fotos_Manual/image-66.png" width="1000"/>

#### Escáner de QR Dispositivos

En este caso aplica igual al de funcionarios. Se escaneará el QR del dispositivo registrado en el módulo de dispositivos.

Ejemplo de QR de Luisa:

<img src="Public/img/Fotos_Manual/image-67.png" width="1000"/>

Escáner de QR dispositivos:

<img src="Public/img/Fotos_Manual/image-68.png" width="1000"/>

Escaneamos el QR de dispositivo de Luisa:

<img src="Public/img/Fotos_Manual/image-69.png" width="1000"/>

Nos dejará visualizar la imagen del funcionario y sus dispositivos registrados:

<img src="Public/img/Fotos_Manual/image-70.png" width="1000"/>

Por último, podemos visualizar el control del dispositivo:

<img src="Public/img/Fotos_Manual/image-71.png" width="1000"/>

---

## 7. Autores

| Nombre | GitHub |
|--------|--------|
| Andres Camilo Carrillo Jaimes | [@usuario](https://github.com) |
| Michael David Montoya Pasachoa | [@usuario](https://github.com) |
| Anderson Estiven Moreno Pinzon | [@usuario](https://github.com) |

> **Tutora:** Elsa María Junca Bernal