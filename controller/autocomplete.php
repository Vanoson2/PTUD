<?php
header('Content-Type: application/json; charset=UTF-8');

// Get query parameter
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// Return empty if query is too short
if (strlen($query) < 1) {
    echo json_encode(['suggestions' => []]);
    exit;
}

// Get suggestions from local JSON file only
$suggestions = getLocalSuggestions($query, 8);

echo json_encode(['suggestions' => $suggestions]);

function getLocalSuggestions($query, $limit = 8) {
    $file = __DIR__ . '/../data/locations_vn.json';
    if (!file_exists($file)) return [];
    $data = json_decode(file_get_contents($file), true);
    if (!$data) return [];

    $normQuery = vn_norm($query);
    $results = [];

    // 1. Search cities (priority)
    if (isset($data['cities'])) {
        foreach ($data['cities'] as $city) {
            $label = $city['name'] . ', ' . $city['province'];
            $normName = vn_norm($city['name']);
            if (strpos($normName, $normQuery) === 0) {
                $results[] = $label;
                if (count($results) >= $limit) return $results;
            }
        }
    }

    // 2. Search attractions (điểm du lịch)
    if (isset($data['attractions'])) {
        foreach ($data['attractions'] as $attraction) {
            $label = $attraction['name'];
            if (!empty($attraction['city'])) {
                $label .= ', thành phố ' . $attraction['city'];
            }
            $label .= ', ' . $attraction['province'];
            $normName = vn_norm($attraction['name']);
            if (strpos($normName, $normQuery) === 0) {
                if (!in_array($label, $results)) {
                    $results[] = $label;
                    if (count($results) >= $limit) return $results;
                }
            }
        }
    }

    // 3. Search districts (quận/huyện)
    if (isset($data['districts'])) {
        foreach ($data['districts'] as $district) {
            $label = $district['name'] . ', thành phố ' . $district['city'] . ', ' . $district['province'];
            $normName = vn_norm($district['name']);
            if (strpos($normName, $normQuery) === 0) {
                if (!in_array($label, $results)) {
                    $results[] = $label;
                    if (count($results) >= $limit) return $results;
                }
            }
        }
    }

    // 4. Search areas (khu vực nổi bật)
    if (isset($data['areas'])) {
        foreach ($data['areas'] as $area) {
            $label = $area['name'] . ', thành phố ' . $area['city'] . ', ' . $area['province'];
            $normName = vn_norm($area['name']);
            if (strpos($normName, $normQuery) === 0) {
                if (!in_array($label, $results)) {
                    $results[] = $label;
                    if (count($results) >= $limit) return $results;
                }
            }
        }
    }

    // 5. Search provinces (last)
    if (isset($data['provinces'])) {
        foreach ($data['provinces'] as $province) {
            $normProvince = vn_norm($province);
            if (strpos($normProvince, $normQuery) === 0) {
                if (!in_array($province, $results)) {
                    $results[] = $province;
                    if (count($results) >= $limit) return $results;
                }
            }
        }
    }

    return $results;
}

function vn_norm($str) {
    $str = mb_strtolower($str, 'UTF-8');
    $replacements = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y',
        'đ'=>'d'
    ];
    $str = strtr($str, $replacements);
    $str = preg_replace('/\s+/', ' ', $str);
    return trim($str);
}

