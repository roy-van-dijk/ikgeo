<?php require_once("functions.php") ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AmsterPark</title>
    <script src='https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.js'></script>
    <link href='https://api.mapbox.com/mapbox-gl-js/v0.53.0/mapbox-gl.css' rel='stylesheet' />
    <link rel="icon" type="image/png" href="assets/logo.png" />
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="spinner">

</div>
<nav>
    <div class="logo">
        <span class="red">Amster</span><span class="black">Park</span>
    </div>
</nav>
<div id="hamburger">
    <div class="bar"></div>
    <div class="bar"></div>
    <div class="bar"></div>
</div>
<div id="menu">
    <h1>Options</h1>
    <div class="setting">
        <div class="title">Map style</div>
        <select class="form-control" name="" id="map-style">
            <option value="light-v10">Light</option>
            <option value="dark-v10">Dark</option>
            <option value="satellite-v9">Satellite</option>
        </select>
    </div>
    <div class="setting">
        <div class="title">Clustering</div>
        <select class="form-control" name="" id="clustering">
            <option value="true">Enabled</option>
            <option value="false">Disabled</option>
        </select>
    </div>
    <div class="setting">
        <div class="title">Overlap</div>
        <select class="form-control" name="" id="overlap">
            <option value="false" selected="selected">Disabled</option>
            <option value="true">Enabled</option>
        </select>
    </div>
    <div class="setting">
        <div class="title">Filter parking garages</div>
        <select class="form-control" name="" id="spot-filter">
            <option value="0">0 spots</option>
            <option value="10">Under 10 spots</option>
            <option value="25">Under 25 spots</option>
            <option value="50">Under 50 spots</option>
            <option value="100">Under 100 spots</option>
            <option value="10000" selected="selected">Don't hide</option>
        </select>
    </div>
    <div class="setting">
        <button id="clear-directions">Clear directions</button>
    </div>
</div>
<div class="content">