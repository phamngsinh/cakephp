<?php

class User extends AppModel {
var $name = 'User';
var $actsAs = array('Media.Transfer', 'Media.Coupler', 'Media.Meta');
}