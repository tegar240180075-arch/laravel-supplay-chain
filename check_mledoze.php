<?php
$data = json_decode(file_get_contents('https://raw.githubusercontent.com/mledoze/countries/master/countries.json'), true);
var_dump(array_intersect_key($data[0], array_flip(['name', 'cca2', 'region', 'latlng', 'currency', 'currencies'])));
