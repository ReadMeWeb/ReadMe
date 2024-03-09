<?php

set_include_path($_SERVER["DOCUMENT_ROOT"]);
require_once 'database.php';

function redirect($url): void {
  header('Location: ' . $url);
  exit();
}

function is_not_signed_in(): bool {
  return $_SESSION['user']['status'] === 'UNREGISTERED';
}

function is_user_signed_in(): bool {
  return $_SESSION['user']['status'] === 'USER';
}

function is_admin_signed_in(): bool {
  return $_SESSION['user']['status'] === 'ADMIN';
}

function extract_from_array_else($key, &$array, $otherwise) {
  $result = array_key_exists($key, $array) ? $array[$key] : $otherwise;
  unset($array[$key]);
  return $result;
}

function upload_file(string $dest_dir, string $uploaded_file_path, string $uploaded_file_name): string {
  if (!is_dir($dest_dir)) {
    mkdir($dest_dir);
  }
  $file_ext = pathinfo($uploaded_file_name, PATHINFO_EXTENSION);
  $file_final_name =  uniqid() . '.' . $file_ext;
  rename($uploaded_file_path, $dest_dir . $file_final_name);
  return $file_final_name;
}

function dbcall($function) {
  $db = null;
  try {
    $db = new Database();
    return $function($db);
  } finally {
    match ($db) {
      null => null,
      default => $db->close(),
    };
  }
}

function arraybreadcrumb($array) {
  $arrayitems = array_map(fn ($item) => new BreadcrumbItem($item), $array);
  $arrayitems[$last = array_key_last($array)] = new BreadcrumbItem($array[$last], isCurrent: true);
  $builder = new BreadcrumbsBuilder();
  foreach ($arrayitems as $i) {
    $builder->addBreadcrumb($i);
  }
  return $builder->build()->getBreadcrumbsHtml();
}
