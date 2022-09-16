<?php
require_once __DIR__ . "/alert_model.php";

foreach (alert::get() as $flash) {
    printf(
      "<div class='alert alert-%s alert-dismissible' role='alert'>%s<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button></div>",
      $flash["type"],
      $flash["message"]
    );
}