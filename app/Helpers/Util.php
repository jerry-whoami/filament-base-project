<?php

namespace App\Helpers;

use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\ActivityLogger;

class Util
{

  public static function getActivityLog($event=""): ActivityLogger
  {
    return activity()
      ->event($event ? $event : "unknown")
      ->causedBy(auth()->user())
      ->withProperties(['ip' => request()->ip()]);
  }

  public static function varexport($expression, $return = FALSE)
  {
    $export = var_export($expression, TRUE);
    $patterns = [
      "/array \(/" => '[',
      "/^([ ]*)\)(,?)$/m" => '$1]$2',
      "/=>[ ]?\n[ ]+\[/" => '=> [',
      "/([ ]*)(\'[^\']+\') => ([\[\'])/" => '$1$2 => $3',
    ];
    $patterns["/[0-9]+ => \[/"] = '[';
    $export = preg_replace(array_keys($patterns), array_values($patterns), $export);
    if ((bool)$return) return $export;
    else echo $export;
  }

  public static function filamentNotifications(array $messages, $level = "success", $send = true)
  {
    foreach ($messages as $message) {
      static::filamentNotification($message, $level, $send);
    }
  }

  public static function filamentNotification($message, $level = "success", $send = true)
  {
    if (str_starts_with($message, "!")) {
      $message = Arr::get(config("appx.messages"), str_replace("!", "", $message));
    }
    $n = Notification::make()->$level()->title($message);
    if ($send) {
      $n->send();
    }
  }

  public static function logChange($message, $level = null, $category = "GENERAL", $data = null)
  {
    $ip = request()->getClientIp();
    if (!$level) {
      $level = "info";
    }
    $m = "{$category}|{$ip}|{$message}";
    if (is_array($data)) {
      $m .= "|" . json_encode($data);
    }
    Log::channel('changes')->$level($m);
  }

  public static function getMessage($key, $default = null, $prefix = "", $suffix = "", $seperator = " : ")
  {
    $msg = config("appx.messages.{$key}", $default);
    if ($prefix) {
      $msg = "{$prefix}{$seperator}{$msg}";
    }
    if ($suffix) {
      $msg = "{$msg}{$seperator}{$suffix}";
    }
    return $msg;
  }
}
