<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Country;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $ports = [
            // ===== ASIA =====
            // China
            ['name' => 'Port of Shanghai', 'country_code' => 'CN', 'lat' => 30.6225, 'lng' => 122.0625, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Shenzhen', 'country_code' => 'CN', 'lat' => 22.5036, 'lng' => 113.8821, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Ningbo-Zhoushan', 'country_code' => 'CN', 'lat' => 29.9486, 'lng' => 121.8900, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Guangzhou', 'country_code' => 'CN', 'lat' => 22.7396, 'lng' => 113.5823, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Qingdao', 'country_code' => 'CN', 'lat' => 36.0674, 'lng' => 120.3826, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Tianjin', 'country_code' => 'CN', 'lat' => 38.9895, 'lng' => 117.7352, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Dalian', 'country_code' => 'CN', 'lat' => 38.9272, 'lng' => 121.6389, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Xiamen', 'country_code' => 'CN', 'lat' => 24.4577, 'lng' => 118.0743, 'type' => 'Seaport', 'size' => 'Large'],

            // Singapore
            ['name' => 'Port of Singapore', 'country_code' => 'SG', 'lat' => 1.2646, 'lng' => 103.8200, 'type' => 'Seaport', 'size' => 'Very Large'],

            // Japan
            ['name' => 'Port of Tokyo', 'country_code' => 'JP', 'lat' => 35.6175, 'lng' => 139.7828, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Yokohama', 'country_code' => 'JP', 'lat' => 35.4520, 'lng' => 139.6437, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Nagoya', 'country_code' => 'JP', 'lat' => 35.0665, 'lng' => 136.8814, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Kobe', 'country_code' => 'JP', 'lat' => 34.6714, 'lng' => 135.1960, 'type' => 'Seaport', 'size' => 'Large'],

            // South Korea
            ['name' => 'Port of Busan', 'country_code' => 'KR', 'lat' => 35.1017, 'lng' => 129.0345, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Incheon', 'country_code' => 'KR', 'lat' => 37.4563, 'lng' => 126.5952, 'type' => 'Seaport', 'size' => 'Large'],

            // Taiwan
            ['name' => 'Port of Kaohsiung', 'country_code' => 'TW', 'lat' => 22.5694, 'lng' => 120.3013, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Taipei (Keelung)', 'country_code' => 'TW', 'lat' => 25.1552, 'lng' => 121.7407, 'type' => 'Seaport', 'size' => 'Medium'],

            // Malaysia
            ['name' => 'Port Klang', 'country_code' => 'MY', 'lat' => 3.0000, 'lng' => 101.3900, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Tanjung Pelepas', 'country_code' => 'MY', 'lat' => 1.3631, 'lng' => 103.5506, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Penang', 'country_code' => 'MY', 'lat' => 5.4216, 'lng' => 100.3465, 'type' => 'Seaport', 'size' => 'Medium'],

            // Thailand
            ['name' => 'Laem Chabang Port', 'country_code' => 'TH', 'lat' => 13.0837, 'lng' => 100.8859, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Bangkok Port (Khlong Toei)', 'country_code' => 'TH', 'lat' => 13.7070, 'lng' => 100.5760, 'type' => 'Seaport', 'size' => 'Medium'],

            // Vietnam
            ['name' => 'Cai Mep International Terminal', 'country_code' => 'VN', 'lat' => 10.4900, 'lng' => 107.0125, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Hai Phong', 'country_code' => 'VN', 'lat' => 20.8628, 'lng' => 106.6784, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Ho Chi Minh City Port', 'country_code' => 'VN', 'lat' => 10.7639, 'lng' => 106.7168, 'type' => 'Seaport', 'size' => 'Large'],

            // Indonesia
            ['name' => 'Port of Tanjung Priok', 'country_code' => 'ID', 'lat' => -6.1042, 'lng' => 106.8796, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Tanjung Perak', 'country_code' => 'ID', 'lat' => -7.2024, 'lng' => 112.7325, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Belawan', 'country_code' => 'ID', 'lat' => 3.7750, 'lng' => 98.6876, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Makassar', 'country_code' => 'ID', 'lat' => -5.1170, 'lng' => 119.4304, 'type' => 'Seaport', 'size' => 'Medium'],

            // Philippines
            ['name' => 'Port of Manila', 'country_code' => 'PH', 'lat' => 14.5839, 'lng' => 120.9625, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Cebu', 'country_code' => 'PH', 'lat' => 10.2987, 'lng' => 123.8950, 'type' => 'Seaport', 'size' => 'Medium'],

            // India
            ['name' => 'Port of Mumbai (JNPT)', 'country_code' => 'IN', 'lat' => 18.9438, 'lng' => 72.8430, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Chennai', 'country_code' => 'IN', 'lat' => 13.0836, 'lng' => 80.2939, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Mundra', 'country_code' => 'IN', 'lat' => 22.8394, 'lng' => 69.7050, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Vishakhapatnam', 'country_code' => 'IN', 'lat' => 17.6869, 'lng' => 83.2186, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Kolkata', 'country_code' => 'IN', 'lat' => 22.5506, 'lng' => 88.3303, 'type' => 'Seaport', 'size' => 'Large'],

            // Bangladesh
            ['name' => 'Port of Chittagong', 'country_code' => 'BD', 'lat' => 22.3289, 'lng' => 91.8121, 'type' => 'Seaport', 'size' => 'Large'],

            // Pakistan
            ['name' => 'Port of Karachi', 'country_code' => 'PK', 'lat' => 24.8465, 'lng' => 66.9847, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Gwadar', 'country_code' => 'PK', 'lat' => 25.1264, 'lng' => 62.3225, 'type' => 'Seaport', 'size' => 'Medium'],

            // Sri Lanka
            ['name' => 'Port of Colombo', 'country_code' => 'LK', 'lat' => 6.9480, 'lng' => 79.8428, 'type' => 'Seaport', 'size' => 'Large'],

            // Myanmar
            ['name' => 'Port of Yangon', 'country_code' => 'MM', 'lat' => 16.8501, 'lng' => 96.1842, 'type' => 'Seaport', 'size' => 'Medium'],

            // ===== MIDDLE EAST =====
            ['name' => 'Jebel Ali Port', 'country_code' => 'AE', 'lat' => 25.0150, 'lng' => 55.0600, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Khalifa', 'country_code' => 'AE', 'lat' => 24.8080, 'lng' => 54.6480, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Jeddah Islamic Port', 'country_code' => 'SA', 'lat' => 21.4697, 'lng' => 39.1672, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'King Abdullah Port', 'country_code' => 'SA', 'lat' => 22.9575, 'lng' => 38.9642, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Hamad', 'country_code' => 'QA', 'lat' => 25.0168, 'lng' => 51.6021, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Salalah Port', 'country_code' => 'OM', 'lat' => 16.9410, 'lng' => 54.0085, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Bandar Abbas', 'country_code' => 'IR', 'lat' => 27.1832, 'lng' => 56.2733, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Haifa', 'country_code' => 'IL', 'lat' => 32.8191, 'lng' => 34.9983, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Umm Qasr Port', 'country_code' => 'IQ', 'lat' => 30.0362, 'lng' => 47.9449, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Shuwaikh', 'country_code' => 'KW', 'lat' => 29.3500, 'lng' => 47.9275, 'type' => 'Seaport', 'size' => 'Medium'],

            // ===== EUROPE =====
            ['name' => 'Port of Rotterdam', 'country_code' => 'NL', 'lat' => 51.9486, 'lng' => 4.1439, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Antwerp-Bruges', 'country_code' => 'BE', 'lat' => 51.2703, 'lng' => 4.3358, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Hamburg', 'country_code' => 'DE', 'lat' => 53.5350, 'lng' => 9.9723, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Bremerhaven', 'country_code' => 'DE', 'lat' => 53.5578, 'lng' => 8.5640, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Felixstowe', 'country_code' => 'GB', 'lat' => 51.9567, 'lng' => 1.3122, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Southampton', 'country_code' => 'GB', 'lat' => 50.8968, 'lng' => -1.3976, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of London', 'country_code' => 'GB', 'lat' => 51.4494, 'lng' => 0.3540, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Le Havre', 'country_code' => 'FR', 'lat' => 49.4862, 'lng' => 0.1197, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Marseille', 'country_code' => 'FR', 'lat' => 43.3263, 'lng' => 5.3445, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Genoa', 'country_code' => 'IT', 'lat' => 44.4071, 'lng' => 8.9189, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Gioia Tauro', 'country_code' => 'IT', 'lat' => 38.4262, 'lng' => 15.8972, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Valencia', 'country_code' => 'ES', 'lat' => 39.4444, 'lng' => -0.3168, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Algeciras', 'country_code' => 'ES', 'lat' => 36.1281, 'lng' => -5.4437, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Barcelona', 'country_code' => 'ES', 'lat' => 41.3565, 'lng' => 2.1631, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Piraeus', 'country_code' => 'GR', 'lat' => 37.9441, 'lng' => 23.6319, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Lisbon (Sines)', 'country_code' => 'PT', 'lat' => 37.9500, 'lng' => -8.8667, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Gdansk', 'country_code' => 'PL', 'lat' => 54.3960, 'lng' => 18.6596, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Gothenburg', 'country_code' => 'SE', 'lat' => 57.6959, 'lng' => 11.9134, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Oslo', 'country_code' => 'NO', 'lat' => 59.9007, 'lng' => 10.7400, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Copenhagen (Malmö)', 'country_code' => 'DK', 'lat' => 55.6934, 'lng' => 12.6138, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Helsinki', 'country_code' => 'FI', 'lat' => 60.1536, 'lng' => 24.9600, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Ambarli Port', 'country_code' => 'TR', 'lat' => 40.9632, 'lng' => 28.6795, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Mersin', 'country_code' => 'TR', 'lat' => 36.7875, 'lng' => 34.6323, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Novorossiysk', 'country_code' => 'RU', 'lat' => 44.7239, 'lng' => 37.7818, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Saint Petersburg', 'country_code' => 'RU', 'lat' => 59.8825, 'lng' => 30.2540, 'type' => 'Seaport', 'size' => 'Large'],

            // ===== AMERICAS =====
            // United States
            ['name' => 'Port of Los Angeles', 'country_code' => 'US', 'lat' => 33.7288, 'lng' => -118.2620, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Long Beach', 'country_code' => 'US', 'lat' => 33.7542, 'lng' => -118.2165, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of New York/New Jersey', 'country_code' => 'US', 'lat' => 40.6689, 'lng' => -74.0445, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Savannah', 'country_code' => 'US', 'lat' => 32.0835, 'lng' => -81.0998, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Houston', 'country_code' => 'US', 'lat' => 29.7355, 'lng' => -95.2693, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Seattle-Tacoma', 'country_code' => 'US', 'lat' => 47.2723, 'lng' => -122.4135, 'type' => 'Seaport', 'size' => 'Large'],

            // Canada
            ['name' => 'Port of Vancouver', 'country_code' => 'CA', 'lat' => 49.2882, 'lng' => -123.1118, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Montreal', 'country_code' => 'CA', 'lat' => 45.5564, 'lng' => -73.5190, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Halifax', 'country_code' => 'CA', 'lat' => 44.6339, 'lng' => -63.5660, 'type' => 'Seaport', 'size' => 'Medium'],

            // Mexico
            ['name' => 'Port of Manzanillo', 'country_code' => 'MX', 'lat' => 19.0530, 'lng' => -104.3148, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Lazaro Cardenas', 'country_code' => 'MX', 'lat' => 17.9447, 'lng' => -102.1805, 'type' => 'Seaport', 'size' => 'Large'],

            // Brazil
            ['name' => 'Port of Santos', 'country_code' => 'BR', 'lat' => -23.9575, 'lng' => -46.3039, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Paranaguá', 'country_code' => 'BR', 'lat' => -25.5151, 'lng' => -48.5013, 'type' => 'Seaport', 'size' => 'Large'],

            // Panama
            ['name' => 'Balboa Port (Panama Canal)', 'country_code' => 'PA', 'lat' => 8.9573, 'lng' => -79.5637, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Colón Port (Panama Canal)', 'country_code' => 'PA', 'lat' => 9.3547, 'lng' => -79.9009, 'type' => 'Seaport', 'size' => 'Large'],

            // Argentina
            ['name' => 'Port of Buenos Aires', 'country_code' => 'AR', 'lat' => -34.6083, 'lng' => -58.3628, 'type' => 'Seaport', 'size' => 'Large'],

            // Chile
            ['name' => 'Port of San Antonio', 'country_code' => 'CL', 'lat' => -33.5940, 'lng' => -71.6105, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Valparaíso', 'country_code' => 'CL', 'lat' => -33.0362, 'lng' => -71.6279, 'type' => 'Seaport', 'size' => 'Medium'],

            // Colombia
            ['name' => 'Port of Cartagena', 'country_code' => 'CO', 'lat' => 10.3930, 'lng' => -75.5142, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Buenaventura', 'country_code' => 'CO', 'lat' => 3.8941, 'lng' => -77.0737, 'type' => 'Seaport', 'size' => 'Medium'],

            // ===== AFRICA =====
            ['name' => 'Port of Durban', 'country_code' => 'ZA', 'lat' => -29.8679, 'lng' => 31.0359, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Cape Town', 'country_code' => 'ZA', 'lat' => -33.9021, 'lng' => 18.4366, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port Said', 'country_code' => 'EG', 'lat' => 31.2565, 'lng' => 32.2841, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Alexandria', 'country_code' => 'EG', 'lat' => 31.1937, 'lng' => 29.8528, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Apapa Port (Lagos)', 'country_code' => 'NG', 'lat' => 6.4360, 'lng' => 3.3598, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Tin Can Island Port (Lagos)', 'country_code' => 'NG', 'lat' => 6.4274, 'lng' => 3.3504, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Mombasa', 'country_code' => 'KE', 'lat' => -4.0478, 'lng' => 39.6665, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Tanger Med Port', 'country_code' => 'MA', 'lat' => 35.8870, 'lng' => -5.5034, 'type' => 'Seaport', 'size' => 'Very Large'],
            ['name' => 'Port of Dar es Salaam', 'country_code' => 'TZ', 'lat' => -6.8297, 'lng' => 39.2907, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Tema', 'country_code' => 'GH', 'lat' => 5.6277, 'lng' => -0.0087, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Maputo', 'country_code' => 'MZ', 'lat' => -25.9667, 'lng' => 32.5833, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Djibouti', 'country_code' => 'DJ', 'lat' => 11.5938, 'lng' => 43.1320, 'type' => 'Seaport', 'size' => 'Large'],

            // ===== OCEANIA =====
            ['name' => 'Port of Melbourne', 'country_code' => 'AU', 'lat' => -37.8202, 'lng' => 144.9142, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Sydney', 'country_code' => 'AU', 'lat' => -33.8478, 'lng' => 151.2225, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Brisbane', 'country_code' => 'AU', 'lat' => -27.3708, 'lng' => 153.1681, 'type' => 'Seaport', 'size' => 'Large'],
            ['name' => 'Port of Fremantle', 'country_code' => 'AU', 'lat' => -32.0569, 'lng' => 115.7439, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Auckland', 'country_code' => 'NZ', 'lat' => -36.8407, 'lng' => 174.7790, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Tauranga', 'country_code' => 'NZ', 'lat' => -37.6433, 'lng' => 176.1786, 'type' => 'Seaport', 'size' => 'Medium'],
            ['name' => 'Port of Lae', 'country_code' => 'PG', 'lat' => -6.7333, 'lng' => 147.0000, 'type' => 'Seaport', 'size' => 'Small'],
            ['name' => 'Port of Suva', 'country_code' => 'FJ', 'lat' => -18.1337, 'lng' => 178.4271, 'type' => 'Seaport', 'size' => 'Small'],
        ];

        $inserted = 0;
        $skipped = 0;

        foreach ($ports as $portData) {
            $country = Country::where('code', $portData['country_code'])->first();

            if (!$country) {
                $skipped++;
                if (app()->runningInConsole()) {
                    echo "  ⚠ Skipped: {$portData['name']} — country '{$portData['country_code']}' not found\n";
                }
                continue;
            }

            // Use updateOrInsert for idempotency — no duplicates on re-run
            DB::table('ports')->updateOrInsert(
                [
                    'name' => $portData['name'],
                    'country_id' => $country->id,
                ],
                [
                    'lat' => $portData['lat'],
                    'lng' => $portData['lng'],
                    'type' => $portData['type'],
                    'size' => $portData['size'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $inserted++;
        }

        if (app()->runningInConsole()) {
            echo "  ✅ Ports seeded: {$inserted} inserted/updated, {$skipped} skipped\n";
        }
    }
}
