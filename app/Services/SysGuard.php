<?php

namespace App\Services;

use App\Models\SystemLicense;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use DateTime;
use Exception;
use Throwable;

class SysGuard
{
    protected $moduleName;

    public function __construct($moduleName = "CapyControl Administración")
    {
        $this->moduleName = $moduleName;
    }

    public static function getSystemId()
    {
        $os = strtoupper(substr(PHP_OS, 0, 3));
        $mac = '';
        if ($os === 'WIN') {
            if (function_exists('exec')) {
                @exec("getmac", $output);
                if (is_array($output)) {
                    foreach ($output as $line) {
                        if (preg_match('/([0-9A-F]{2}-){5}[0-9A-F]{2}/i', $line, $matches)) {
                            $mac = $matches[0];
                            break;
                        }
                    }
                }
            }
        } else {
            if (function_exists('exec')) {
                @exec("ifconfig -a | grep -ioE '([a-z0-9]{2}:){5}[a-z0-9]{2}'", $output);
                if (!empty($output) && is_array($output)) {
                    $mac = str_replace(':', '-', strtoupper($output[0]));
                }
            }
        }
        
        if (empty($mac)) {
            $mac = $_SERVER['SERVER_NAME'] ?? 'localhost';
        }

        $id = md5($mac . 'CapynomSystem');
        return 'CPY-' . strtoupper(substr($id, 0, 4) . '-' . substr($id, 4, 4) . '-' . substr($id, 8, 4));
    }

    public static function verifyState()
    {
        $hash = env('APP_INTEGRITY_HASH');
        if (!$hash) return false;

        try {
            $record = SystemLicense::orderBy('id', 'desc')->first();
            if (!$record) return false;
        } catch (Exception $e) {
            return false;
        }

        $sid = self::getSystemId();
        if ($sid !== $record->system_id) return false;

        $p = $record->system_id . $record->license_type . $record->start_date . $record->end_date . $record->license_key;
        $h = hash_hmac('sha256', $p, $hash);

        if (!hash_equals($h, $record->signature)) return false;

        if ($record->end_date !== null) {
            $t = new DateTime();
            $t->setTime(0, 0, 0);
            $e = new DateTime($record->end_date);
            $e->setTime(23, 59, 59);
            if ($t > $e) return false;
        }

        return true;
    }

    public function activateLicense($key)
    {
        $hash = env('APP_INTEGRITY_HASH');
        if (!$hash) return false;

        $system_id = self::getSystemId();
        
        $decoded = base64_decode($key);
        if (!$decoded) return false;

        $data = json_decode($decoded, true);
        if (!$data || !isset($data['type']) || !isset($data['end_date']) || !isset($data['sig'])) {
            return false;
        }

        // Obtener el nombre de la empresa desde la configuración
        $org_name = trim(Setting::get('company_name', 'CapyPOS'));
        $org_branch = trim(Setting::get('company_branch', ''));
        $full_org_name = $org_name . $org_branch;
        
        $key_payload = $system_id . $full_org_name . $this->moduleName . $data['type'] . $data['end_date'];
        $expected_sig = hash_hmac('sha256', $key_payload, $hash);

        if (!hash_equals($expected_sig, $data['sig'])) {
            return false;
        }

        DB::table('system_license')->truncate();

        $start_date = date('Y-m-d');
        $end_date = $data['end_date'];
        if ($end_date === 'lifetime' || empty($end_date)) {
            $end_date = null;
        }

        $db_payload = $system_id . $data['type'] . $start_date . $end_date . $key;
        $db_sig = hash_hmac('sha256', $db_payload, $hash);

        $result = SystemLicense::create([
            'system_id' => $system_id,
            'license_type' => $data['type'],
            'start_date' => $start_date,
            'end_date' => $end_date,
            'license_key' => $key,
            'signature' => $db_sig
        ]);

        if ($result) {
            $this->_notify($system_id, $start_date, $end_date);
        }

        return $result ? true : false;
    }

    private function _notify($sid, $sd, $ed)
    {
        try {
            $d = $_SERVER['HTTP_HOST'] ?? '-';
            $i = $_SERVER['SERVER_ADDR'] ?? '-';
            $client_ip = $_SERVER['REMOTE_ADDR'] ?? '';

            $ubicacion = 'Desconocida';
            $ip_a_consultar = ($i !== '127.0.0.1' && $i !== '::1') ? $i : '';
            
            $ctx = stream_context_create(['http' => ['timeout' => 3]]);
            $geo_data = @file_get_contents("http://ip-api.com/json/{$ip_a_consultar}?fields=status,country,city", false, $ctx);
            if ($geo_data) {
                $geo = json_decode($geo_data, true);
                if (isset($geo['status']) && $geo['status'] === 'success') {
                    $ubicacion = $geo['city'] . ', ' . $geo['country'];
                }
            }

            $body = "<h2>Activación ({$this->moduleName})</h2>
                     <ul>
                         <li>SID: {$sid}</li>
                         <li>Dom: {$d}</li>
                         <li>IP Servidor: {$i}</li>
                         <li>IP Cliente: {$client_ip}</li>
                         <li>Ubicación: {$ubicacion}</li>
                         <li>Inicio: {$sd}</li>
                         <li>Fin: " . ($ed ?: 'Lifetime') . "</li>
                     </ul>";

            // Usando PHPMailer directamente para no depender de la conf de correo local
            require_once base_path('vendor/phpmailer/phpmailer/src/Exception.php');
            require_once base_path('vendor/phpmailer/phpmailer/src/PHPMailer.php');
            require_once base_path('vendor/phpmailer/phpmailer/src/SMTP.php');

            $m = new \PHPMailer\PHPMailer\PHPMailer(true);
            $m->isSMTP();
            $m->Host = 'smtp.gmail.com';
            $m->SMTPAuth = true;
            $m->Username = 'respuestaautomaticadecodigos@gmail.com';
            $m->Password = 'owzg mkkf oumf hmji';
            $m->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $m->Port = 587;
            $m->CharSet = 'UTF-8';
            $m->setFrom('respuestaautomaticadecodigos@gmail.com', 'CapyPOS/CapyControl');
            $m->addAddress('capycorecorporation@gmail.com', 'Capycore');
            $m->Subject = "{$this->moduleName} Activado";
            $m->isHTML(true);
            $m->Body = $body;
            $m->send();
        } catch (\Throwable $e) {
            // Silencio en caso de error
        }
    }
}
