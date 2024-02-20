<?php

function redirect($url): void
{
  header('Location: ' . $url);
  exit();
}

function is_not_signed_in(): bool
{
  return $_SESSION['user']['status'] === 'UNREGISTERED';
}

function is_user_signed_in(): bool
{
  return $_SESSION['user']['status'] === 'USER';
}

function is_admin_signed_in(): bool
{
  return $_SESSION['user']['status'] === 'ADMIN';
}

function extract_from_array_else($key, $array, $otherwise)
{
  $result = array_key_exists($key, $array) ? $array[$key] : $otherwise;
  unset($array[$key]);
  return $result;
}

function upload_file(string $dest_dir, string $uploaded_file_path, string $uploaded_file_name): string {
  if(!is_dir($dest_dir)) {
    mkdir($dest_dir);
  }
  $file_ext = pathinfo($uploaded_file_name, PATHINFO_EXTENSION);
  $file_final_name =  uniqid() . '.' . $file_ext;
  rename($uploaded_file_path, $dest_dir . $file_final_name);
  return $file_final_name;
}

