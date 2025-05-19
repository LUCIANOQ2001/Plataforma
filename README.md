# Plataforma
Proyecto de realizar un nuevo diseño para el salón virtual del CIP

el .htaccess es:
Options All -Indexes

RewriteEngine On

# No reescribir si apunta a un fichero o directorio real
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Captura letras, números y guiones, con o sin barra final
RewriteRule ^([A-Za-z0-9-]+)/?$ index.php?views=$1 [L,QSA]


El día de hoy 19 de Mayo del 2025 se realizó las siguientes modificaciones:
- Se agregó un nuevo menu "Historial de Consultas" para docentes y "Nueva Consulta" para estudiantes en el sidebar.
- Como estudiante se puede realizar una nueva consulta y visualizar el historial de consultas que ha hecho hasta la fecha actual.
- Como docente se puede visualizar la consulta que los estudiantes han realizado y cambiar el estado de "pendiente" y "respondido".
