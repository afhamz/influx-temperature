# influx-temperature

This repository contains our last project for Database Management System Course in Universitas Gadjah Mada. We are told to use a time-series database, so we use InfluxDB in PHP.

We used InfluxDB Client in PHP from influxdb-php (https://github.com/influxdata/influxdb-php). The project is simply described as follows:
1. grab the current temperature data in Yogyakarta (Indonesia) using API from http://openweathermap.org/ for every 20 seconds.
2. save the delivered data into InfluxDB.
3. illustrate the temperature data using chart library from www.highcharts.com.
