<?php

/**
 * Fallback when the host document root is the project root instead of public/.
 * Prefer pointing the vhost/subdomain document root at public/ in production.
 */
require __DIR__.'/public/index.php';
