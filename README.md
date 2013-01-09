The Transit Screen code is in its early stages, so future code changes
to improve reliability and coding elegance are on the way.  In the
meantime, here are some instructions to help you get started.

All you need to run the code is PHP and and a database.  The code was 
designed to work with PostgreSQL, but adjusting it for MySQL is not that
hard.

The code is written on the Code Igniter platform following a model-view-
controller (MVC) architecture.  

If you wish to have predictions for Metrorail and Metrobus
you will have to obtain a WMATA API key and add it to your screen configuration.

You will likely need to adjust a few configuration files to get the set-
up working properly on your system:

-- application/config/config.php
	You may need to adjust the base_url and index_page elements. If you are
	running on localhost, you will definitely need to do this.

-- application/config/constants.php
	You may need to change the PUBLICDIR constant to match the
	location of your public files directory.  Some Code Igniter
	installation manuals recommend creating rewrites for this 
	directory, in which case you may need to adjust this accordingly.
	If you notice that CSS and javascript files aren't loading properly
	this will be the constant to fix.

-- application/config/database.php
	Here is where you set up your database connection with the appropriate
	login credentials and database name.  Please note that you will have
	to set the correct value for $db['default']['dbdriver'], which is set
	by default to 'postgre'.  If you intend to use MySQL, you will need 
	to change this value.
	
The database schema is defined in the file schema.sql.  If you wish to run
it on MySQL, you may need to adjust it slightly.
