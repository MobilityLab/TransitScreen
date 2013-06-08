# TransitScreen

![Example Transit Screen](http://images.greatergreaterwashington.org/images/201201/051058-1.png)

## Introduction

TransitScreen is a web application real-time display that consolidates information about your transit options. 
This includes transit arrivals (for multiple modes and Washington, DC agencies including Metro, Metrobus, Circulator, ART, and PG The Bus), and 
bikeshare availability. 

As the screens request data about selected stops, the web server queries each transit agency for arrival predictions, then asynchronously provides data to the screens. 

The design aims to be information-dense, high contrast, yet clear.

The screen code is highly robust and successful in long-term deployment.

## Instructions

The code requires PHP and (currently) a PostgreSQL database. The PHP backend is written on the Code Igniter (CI) platform following a model-view-controller (MVC) architecture. The frontend is Javascript. The database schema is defined in the file schema.sql.

You will likely need to adjust a few configuration files to get the set-up working properly. The [Transit Screen wiki](https://github.com/MobilityLab/TransitScreen/wiki) explains what to do, depending on your operating system.
