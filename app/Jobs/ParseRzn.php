<?php

namespace App\Jobs;

use App\License;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use League\CLImate\CLImate;

class ParseRzn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const URL = 'http://www.roszdravnadzor.ru/ajax/services/licenses';

    const REGIONS = [
        4 => 'Алтайский край',
        5 => 'Амурская область',
        6 => 'Архангельская область',
        7 => 'Астраханская область',
        97 => 'Байконур',
        105 => 'Без адреса',
        9 => 'Белгородская область',
        93 => 'Ближнее зарубежье',
        10 => 'Брянская область',
        12 => 'Владимирская область',
        13 => 'Волгоградская область',
        14 => 'Вологодская область',
        15 => 'Воронежская область',
        91 => 'Дальнее зарубежье',
        17 => 'Еврейская автономная область',
        99 => 'Забайкальский край',
        18 => 'Ивановская область',
        20 => 'Иркутская область',
        21 => 'Кабардино-Балкарская Республика',
        22 => 'Калининградская область',
        24 => 'Калужская область',
        25 => 'Камчатский край',
        26 => 'Карачаево-Черкесская Республика',
        28 => 'Кемеровская область - Кузбасс',
        29 => 'Кировская область',
        33 => 'Костромская область',
        34 => 'Краснодарский край',
        35 => 'Красноярский край',
        36 => 'Курганская область',
        37 => 'Курская область',
        38 => 'Ленинградская область',
        39 => 'Липецкая область',
        40 => 'Магаданская область',
        43 => 'Москва',
        44 => 'Московская область',
        45 => 'Мурманская область',
        46 => 'Ненецкий автономный округ',
        47 => 'Нижегородская область',
        48 => 'Новгородская область',
        49 => 'Новосибирская область',
        50 => 'Омская область',
        51 => 'Оренбургская область',
        52 => 'Орловская область',
        53 => 'Пензенская область',
        54 => 'Пермский край',
        55 => 'Приморский край',
        56 => 'Псковская область',
        2 => 'Республика Адыгея',
        3 => 'Республика Алтай',
        8 => 'Республика Башкортостан',
        11 => 'Республика Бурятия',
        16 => 'Республика Дагестан',
        19 => 'Республика Ингушетия',
        23 => 'Республика Калмыкия',
        27 => 'Республика Карелия',
        30 => 'Республика Коми',
        7961 => 'Республика Крым',
        41 => 'Республика Марий Эл',
        42 => 'Республика Мордовия',
        62 => 'Республика Саха (Якутия)',
        65 => 'Республика Северная Осетия-Алания',
        70 => 'Республика Татарстан',
        74 => 'Республика Тыва',
        80 => 'Республика Хакасия',
        57 => 'Ростовская область',
        58 => 'Рязанская область',
        59 => 'Самарская область',
        60 => 'Санкт-Петербург',
        61 => 'Саратовская область',
        63 => 'Сахалинская область',
        64 => 'Свердловская область',
        7962 => 'Севастополь',
        66 => 'Смоленская область',
        67 => 'Ставропольский край',
        69 => 'Тамбовская область',
        71 => 'Тверская область',
        72 => 'Томская область',
        73 => 'Тульская область',
        75 => 'Тюменская область',
        76 => 'Удмуртская Республика',
        77 => 'Ульяновская область',
        79 => 'Хабаровский край',
        81 => 'Ханты-Мансийский автономный округ -  Югра',
        82 => 'Челябинская область',
        83 => 'Чеченская Республика',
        85 => 'Чувашская Республика',
        86 => 'Чукотский автономный округ',
        88 => 'Ямало-Ненецкий автономный округ',
        89 => 'Ярославская область',
    ];

    const YEAR_MIN = 1991;

    protected $regionId, $date;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $regionId, \DateTime $date)
    {
        $this->regionId = $regionId;
        $this->date = $date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client([
            'headers' => [
                'Pragma' => 'no-cache',
                'Origin' => 'http://www.roszdravnadzor.ru',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/77.0.3865.120 Safari/537.36',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Accept' => 'application/json, text/javascript, */*; q=0.01',
                'X-Requested-With' => 'XMLHttpRequest',
                'Connection' => 'keep-alive',
                'Referer' => 'http://www.roszdravnadzor.ru/services/licenses',
            ]
        ]);

        $sleep = (int)env('PARSE_RZN_SLEEP', 0);

        $climate = new CLImate();
        $climate->info('Пауза: ' . $sleep . 'с...');
        sleep($sleep);
        $climate->info('Отправляю зарос (' . static::REGIONS[$this->regionId] . ', ' . $this->date->format('d.m.Y') . ')...');
        $response = $client->post(static::URL, [
            'form_params' => [
                'start' => '0',
                'length' => '100',
                'search' => [
                    'value' => '',
                    'regex' => 'false',
                ],
                'prev_total' => '0',
                'q_no' => '',
                'q_org_ogrn' => '',
                'q_org_label' => '',
                'q_type' => '1',
                'q_active' => '0',
                'dt_from' => $this->date->format('d.m.Y'),
                'dt_to' => $this->date->format('d.m.Y'),
                'q_activity' => '',
                'q_region' => $this->regionId,
                'q_org_inn' => '',
            ]
        ]);
        if ($response->getStatusCode() != 200) {
            throw new \Exception('HTTP-ответ сервера: ' . $response->getStatusCode());
        }
        $climate->info('Получаю данные...');
        $response = (string)$response->getBody();
        if (empty($response)) {
            throw new \Exception('Пустой ответ сервера');
        }
        $climate->info('Данные получены.');
        $climate->info('Парсю данные...');
        $response = json_decode($response);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception('Ошибка парсинга JSON');
        }
        $responseData = $response->data ?? [];
        $climate->info('Данные спарсены. Записей: ' . count($responseData));
        if ($responseData) {
            $climate->info('Сохраняю в БД...');
            foreach ($responseData as $data) {
                if (!License::where('reg_number', $data->col1->label)->count()) {
                    $license = new License();
                    $license->reg_number = $data->col1->label;
                    $license->reg_date = $this->date->format('Y-m-d');
                    $license->name = $data->col3->title ?? ($data->col3->label ?? '');
                    $license->address = $data->col5->title ?? ($data->col5->label ?? '');
                    $license->ogrn = ($data->col6->label) ?? '';
                    $license->inn = ($data->col7->label) ?? '';
                    $license->okpo = ($data->col8->label) ?? '';
                    $license->license_number = ($data->col9->label) ?? '';
                    $license->save();
                }
            }
            $climate->info('Сохранено.');
        }
    }
}
