<?php

namespace Pageflow\Core;

// EDITOR FORM CONSTANTS
use const Pageflow\CMS_ROOT;
use const Pageflow\PRIVATE_DIR;

const ADD_ELEMENT_FORM_ID = 'add_element_type_id';
const EDIT_ELEMENT_HOLDER_ID = 'element_holder_id';
const ACTION_FORM_ID = 'action';
const DELETE_ELEMENT_FORM_ID = 'delete_element';
const ELEMENT_HOLDER_FORM_ID = 'element_holder_form_id';
const ELEMENT_CONTAINER_ASSIGNMENTS_FORM_ID = 'element_container_assignments';
const TARGET_CONTAINER_FORM_ID = 'target_container_id';
const DELETE_CONTAINER_CHILDREN_FORM_ID = 'delete_container_children';

// DEFINE SYSTEM VERSION
const SYSTEM_VERSION = "0.0.7";

// DEFINE TIME OUT
const SESSION_TIMEOUT = 3600;

// DIRECTORIES
const COMPONENT_DIR = PRIVATE_DIR . '/components';
const COMPONENT_TEMP_DIR = COMPONENT_DIR . '/temp';
const UPLOAD_DIR = PRIVATE_DIR . '/upload';
const FRONTEND_TEMPLATE_DIR = PRIVATE_DIR . '/templates';
const CACHE_DIR = PRIVATE_DIR . '/cache';
const BACKEND_TEMPLATE_DIR = CMS_ROOT . '/view/templates';
const STATIC_DIR = CMS_ROOT . '/static';
const CONFIG_DIR = PRIVATE_DIR . '/config';