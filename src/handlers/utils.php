<?

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

