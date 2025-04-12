# EazyPay API

**EazyPay** es una aplicación web que implementa un sistema de gestión de pagos en grupo. Este repositorio se correponde a la API REST de la aplicación.Su objetivo es gestionar gastos compartidos entre usuarios, permitiendo registrar deudas, pagos y consultar balances. Pensada para facilitar la organización económica en grupos, combina buenas prácticas de ingeniería del software y una arquitectura modular.

## 📌 Características principales

- ✅ Registro y autenticación de usuarios  
- 💰 Gestión de grupos, gastos y deudas compartidas  
- 📊 Cálculo de saldos y balances por usuario  
- 🔀 Rutas organizadas mediante arquitectura RESTful  
- 🗂️ Base de datos relacional integrada y exportable  
- ⚙️ Desarrollada sobre un stack LAMP (Linux, Apache, MySQL y PHP) 

## 🚀 Tecnologías utilizadas

- **PHP** (REST)
- **MySQL**
- **Apache HTTP Server**
- **.htaccess** + `URI Dispatcher` para gestión de rutas
- **MVC modularizado** (config, model, rest)

## ⚙️ Instalación y uso

1. **Clona el repositorio**

```bash
git clone https://github.com/tuusuario/EazyPay-API.git
cd EazyPay-API
```

2. **Configura el entorno**

Puedes usar Docker, XAMPP o un entorno LAMP local.

3. **Importa la base de datos**

```sql
SOURCE db.sql;
```

4. **Ejecuta el entorno**

```bash
./run.sh
```

5. **Accede a la API**

```
http://localhost/EazyPay-API/www/rest/
```

## 👨‍💻 Autores

Proyecto desarrollado por estudiantes de Ingeniería Informática – Universidade de Vigo:

- David Álvarez Iglesias
- Pablo Dorrió Vázquez  
- Pablo Arias Campaña  

## 📄 Licencia

Este proyecto se distribuye bajo la licencia MIT.  
Puedes usar, modificar o reutilizar el código citando la autoría original.

## 🤝 Contribuciones

Este proyecto fue creado con fines educativos.
