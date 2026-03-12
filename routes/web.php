<?php

/**
 * routes/web.php
 * 
 * Entry point only  all routes live in separate files.
 *
 *  public.php   landing page + auth (publicly accessible)
 *  admin.php    admin guard routes
 *  user.php     web guard (siswa / guru) routes
 */

require __DIR__ . '/public.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/user.php';
