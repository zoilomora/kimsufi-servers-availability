<?php
declare(strict_types=1);

namespace ZoiloMora\Kimsufi;

use GuzzleHttp\Client;

final class Service
{
    private const AVAILABILITIES_SERVERS = '/engine/api/dedicated/server/availabilities?country=es';
    private const BUY_BASE_URL = 'https://www.kimsufi.com/es/pedido/kemsirve.xml?reference=';

    private Client $ovhClient;
    private Client $telegramClient;

    public function __construct()
    {
        $this->ovhClient = new Client([
            'base_uri' => 'https://www.ovh.com',
        ]);
        $this->telegramClient = new Client([
            'base_uri' => 'https://api.telegram.org',
        ]);
    }

    public function execute(array $regions, array $hardware): void
    {
        $serverAvailability = [];
        $object = $this->getData();

        foreach ($object as $item) {
            if (false === \array_key_exists('hardware', $item) ||
                false === \in_array($item['hardware'], $hardware, true)
            ) {
                continue;
            }

            if (false === \array_key_exists('region', $item) || false === \in_array($item['region'], $regions, true)) {
                continue;
            }

            if (false === \array_key_exists('datacenters', $item)) {
                continue;
            }

            foreach ($item['datacenters'] as $datacenter) {
                if (false === \array_key_exists('availability', $datacenter) ||
                    'unavailable' === $datacenter['availability']
                ) {
                    continue;
                }

                $serverAvailability[] = [
                    'hardware' => $item['hardware'],
                    'region' => $item['region'],
                    'dataCenter' => $datacenter['datacenter'],
                    'availability' => $datacenter['availability'],
                ];
            }
        }

        if (0 === \count($serverAvailability)) {
            return;
        }

        $this->sendAlert($serverAvailability);
    }

    private function getData(): array
    {
        $response = $this->ovhClient->get(self::AVAILABILITIES_SERVERS);

        return \json_decode($response->getBody()->getContents(), true);
    }

    private function generateTelegramMessage(array $servers): string
    {
        $msg = \sprintf("*Available Kimsufi Servers*\n\n");

        foreach ($servers as $server) {
            $msg .= \sprintf("*Hardware:* %s\n", $server['hardware']);
            $msg .= \sprintf("*Region:* %s\n", $server['region']);
            $msg .= \sprintf("*Datacenter:* %s\n", $server['dataCenter']);
            $msg .= \sprintf("*Availability:* %s\n", $server['availability']);
            $msg .= \sprintf("[¡Purchase Link!](%s%s)\n\n", self::BUY_BASE_URL, $server['hardware']);
        }

        return $msg;
    }

    private function sendAlert(array $servers): void
    {
        $this->sendTelegramMessage(
            $this->generateTelegramMessage($servers),
        );
    }

    private function sendTelegramMessage(string $message): void
    {
        $token = TELEGRAM_TOKEN;
        $queryArray = [
            'chat_id' => TELEGRAM_CHAT_ID,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => true,
            'text' => $message,
        ];

        $uri = "/bot{$token}/sendMessage?" . \http_build_query($queryArray);

        $this->telegramClient->get($uri);
    }
}
