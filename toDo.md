# REQUISITOS
- [x] (F1) Registrarse, indicando un alias (sin espacios), una contraseña y un email.
- [x] (F2) Autenticarse, comprobando los credenciales. Una vez autenticado, se va al listado de proyectos (ver F3).
- [x] (F3) Listado de proyectos. Se ve un listado de todos los proyectos donde el usuario está incluido. Una vez se hace clic en un proyecto, se va a F5. Desde aquí también se podrá:
    - [x] (F4) Crear proyecto nuevo, indicando un nombre del proyecto.
    - [x] (F5) Ver proyecto en detalle. En este panel deberá verse un listado de pagos ya realizados. Además, desde este panel se podrá:
        - [x] (F6) Añadir un usuario al proyecto. Se deberá indicar un email del usuario que se quiere añadir.
        - [x] (F7) Crear nuevo pago indicando: (i) usuario que paga (por defecto, el usuario autenticado), (ii) nombre de pago, (iii) cantidad y (iv) otros usuarios del proyecto para los que se hace el pago (por defecto todos, incluido el pagador).
        - [x] (F8) Editar un pago existente, pudiendo cambiar cualquier campo.
        - [x] (F9) Eliminar un pago.
        - [x] (F10) Listado de deudas pendientes entre usuarios. Esta parte muestra qué pagos se deberían realizar entre usuarios para saldar todas las deudas.

# Usar extensión Markdown Checkbox (crear checkbox con - [ ], y marcarlas con - [x])