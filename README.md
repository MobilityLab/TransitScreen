# TransitScreen: an experimental project from the [Mobility Lab](http://mobilitylab.org) 

![Example Transit Screen](http://images.greatergreaterwashington.org/images/201201/051058-1.png)

## Introduction

TransitScreen is a web-based real-time display that shows information about transit options. 
These include transit arrivals (for multiple modes and agencies at once) and 
bikeshare availability. 

In real time, the web server queries each transit agency for the arrival predictions 
for selected stops, then relays the data to the screens. 

The design aims to be information-dense, high contrast, yet clear.

The screen code is highly robust and successful in long-term deployment.

## Instructions

The code currently requires PHP and a PostgreSQL database. The PHP backend is written on the Code Igniter (CI) platform following a model-view-
controller (MVC) architecture. The frontend is simple Javascript.

You will likely need to adjust a few configuration files to get the set-
up working properly on your system.

The [Transit Screen wiki](https://github.com/MobilityLab/TransitScreen/wiki) explains what to do.

The database schema is defined in the file schema.sql.
