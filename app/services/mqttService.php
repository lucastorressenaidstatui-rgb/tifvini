<?php

namespace App\services;

use App\Models\Sensor;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class mqttService
{
    public function receberDados(): void
    {
        $mqtt = new MqttClient(config('mqtt.host'), (int) config('mqtt.port'), 'Aula1-Client'.uniqid());
        $settings = (new ConnectionSettings)
            ->setUsername(config('mqtt.username'))
            ->setPassword(config('mqtt.password'))
            ->setUseTls(true);

        $mqtt->connect($settings, true);
        echo "Laravel conectado ao HiveMQ\n";

        $mqtt->subscribe(config('mqtt.topic'), function (string $topic, string $message): void {
            echo "Mensagem recebida:\n".$message."\n";
            $dados = json_decode($message, true);
            if (! is_array($dados) || ! isset($dados['sensor'], $dados['temperatura'], $dados['umidade'])) {
                echo "Mensagem ignorada: JSON invalido ou campos obrigatorios ausentes.\n";

                return;
            }
            Sensor::create([
                'sensor' => $dados['sensor'],
                'temperatura' => $dados['temperatura'],
                'umidade' => $dados['umidade'],
            ]);
            echo "Dados salvos no banco de dados com sucesso!\n";
        }, 0);

        echo "Aguardando mensagens MQTT...\n";
        $mqtt->loop(true);
        $mqtt->disconnect();
    }
}
