# REQUISITOS
- [x] (F1) Registrarse, indicando un alias (sin espacios), una contraseña y un email.
- [x] (F2) Autenticarse, comprobando los credenciales. Una vez autenticado, se va al listado de proyectos (ver F3).
- [x] (F3) Listado de proyectos. Se ve un listado de todos los proyectos donde el usuario está incluido. Una vez se hace clic en un proyecto, se va a F5. Desde aquí también se podrá:
    - [x] (F4) Crear proyecto nuevo, indicando un nombre del proyecto.
    - [x] (F5) Ver proyecto en detalle. En este panel deberá verse un listado de pagos ya realizados. Además, desde este panel se podrá:
        - [ ] (F6) Añadir un usuario al proyecto. Se deberá indicar un email del usuario que se quiere añadir.
        - [x] (F7) Crear nuevo pago indicando: (i) usuario que paga (por defecto, el usuario autenticado), (ii) nombre de pago, (iii) cantidad y (iv) otros usuarios del proyecto para los que se hace el pago (por defecto todos, incluido el pagador).
        - [ ] (F8) Editar un pago existente, pudiendo cambiar cualquier campo.
        - [x] (F9) Eliminar un pago.
        - [ ] (F10) Listado de deudas pendientes entre usuarios. Esta parte muestra qué pagos se deberían realizar entre usuarios para saldar todas las deudas.


# TAREAS
- [ ] Aplicar CSS a todas las vistas
- [ ] Refactorizar código
- [ ] Internacionalizar todo lo necesario
- [ ] Mostrar solos los grupos a los que pertenece el currentUser
- [ ] Añadir apartado de dividir porcentajes o no en edit expenses
- [ ] Corregir suma de importes de cada participante (50/3 salta el error de que la suma no da)
- [ ] Al editar un gasto que aparezcan las casillas de los participantes marcadas y con los valores previos
- [ ] Boton de back en group
- [ ] Mejorar vista de Group para los balances
- [ ] FUNCIONALIDAD MOVIENTOS SUGERIDOS Y REEMBOLSOS
- [ ] Al editar un gasto que aparezcan las casillas de los participantes marcadas y con los valores previos
- [ ] Al editar un grupo y añadir un participante vacio salta error
- [ ] notificar errores de añadir usuarios repetidos o invalidos en el grupo
- [ ] Notificar todos los errores!!! Por ejemplo al crear un gasto completamente vacio no se crea pero no sale ningun mensaje, y mas casos
- [ ] Si se añade un pago con solo 1 participante que salte error, no tiene sentido, nadie le debe nada
- [ ] REVISAR CODIGO Y PASARLO A LIMPIO, usar mismo procesamiento, logica y estructura de datos en funciones y clases similares, usar la variable errors, manejar bien excepciones y OPTIMIZAR codigo, mucho se puede simplificar.










# Usar extensión Markdown Checkbox (crear checkbox con - [ ], y marcarlas con - [x])