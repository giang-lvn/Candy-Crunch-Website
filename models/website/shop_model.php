<?php
class ShopModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Helper function to extract thumbnail path from JSON image data
     * Image is stored as JSON array with 'path' and 'is_thumbnail' properties
     */
    private function getProductThumbnailPath($imageData): string
    {
        if (empty($imageData)) return '';
        
        // Try to decode JSON
        $decoded = json_decode($imageData, true);
        if (is_array($decoded)) {
            // Find the thumbnail image
            foreach ($decoded as $img) {
                if (isset($img['is_thumbnail']) && $img['is_thumbnail']) {
                    return $img['path'] ?? '';
                }
            }
            // Return first image if no thumbnail is set
            if (!empty($decoded[0])) {
                return is_array($decoded[0]) ? ($decoded[0]['path'] ?? '') : $decoded[0];
            }
            return '';
        }
        
        // Old format: return as-is
        return $imageData;
    }

    public function getProducts(array $params): array
    {
        $where = [];
        $bind = [];
        $having = [];

        // --- 1. SEARCH & FILTER (Theo yêu cầu: product, category, ingredient, flavor, rating) ---
        if (!empty($params['search'])) {
            $where[] = "p.ProductName LIKE :search";
            $bind['search'] = '%' . $params['search'] . '%';
        }

        if (!empty($params['category'])) {
            $cats = explode(',', $params['category']);
            $placeholders = [];
            foreach ($cats as $i => $cat) {
                $key = "cat$i";
                $placeholders[] = ":$key";
                $bind[$key] = $cat;
            }
            $where[] = "c.CategoryName IN (" . implode(',', $placeholders) . ")";
        }

        if (!empty($params['ingredient'])) {
            $where[] = "p.Ingredient LIKE :ingredient";
            $bind['ingredient'] = '%' . $params['ingredient'] . '%';
        }

        if (!empty($params['flavour'])) {
            $where[] = "p.Flavour LIKE :flavour";
            $bind['flavour'] = '%' . $params['flavour'] . '%';
        }

        if (!empty($params['rating'])) {
            $having[] = "IFNULL(AVG(fb.Rating), 0) >= :rating";
            $bind['rating'] = (int) $params['rating'];
        }

        // Filter by Product Type (On sales, New products, Best-seller)
        if (!empty($params['productType'])) {
            $types = explode(',', $params['productType']);
            $placeholders = [];
            foreach ($types as $i => $type) {
                $key = "type$i";
                $placeholders[] = ":$key";
                $bind[$key] = $type;
            }
            $where[] = "p.Filter IN (" . implode(',', $placeholders) . ")";
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $havingSql = $having ? 'HAVING ' . implode(' AND ', $having) : '';

        // --- 2. ĐẾM TỔNG SẢN PHẨM (Dùng Subquery để đếm đúng 19 sản phẩm sau khi lọc) ---
        $countSql = "SELECT COUNT(*) FROM (
            SELECT p.ProductID FROM PRODUCT p
            JOIN CATEGORY c ON p.CategoryID = c.CategoryID
            LEFT JOIN SKU s ON p.ProductID = s.ProductID
            LEFT JOIN FEEDBACK fb ON fb.SKUID = s.SKUID
            $whereSql GROUP BY p.ProductID $havingSql
        ) AS total_query";

        $stmtCount = $this->db->prepare($countSql);
        foreach ($bind as $k => $v) {
            $stmtCount->bindValue($k, $v);
        }
        $stmtCount->execute();
        $total = (int) $stmtCount->fetchColumn();

        // --- 3. SORTING LOGIC ---
        $sortMap = [
            'price_asc' => 'MinPrice ASC',
            'price_desc' => 'MinPrice DESC',
            'rating' => 'AvgRating DESC',
            'name' => 'p.ProductName ASC'
        ];
        $orderBy = $sortMap[$params['sort'] ?? ''] ?? 'p.ProductName ASC';

        // --- 4. PHÂN TRANG ---
        $page = max(1, (int) ($params['page'] ?? 1));
        $perPage = (int) ($params['per_page'] ?? 20);
        $offset = ($page - 1) * $perPage;

        // --- 5. MAIN QUERY (Lấy p.Image và đóng gói SKU chi tiết) ---
        $sql = "SELECT 
                    p.ProductID, p.ProductName, p.Image, p.Ingredient, p.Flavour, p.Filter, c.CategoryName,
                    MIN(IFNULL(s.PromotionPrice, s.OriginalPrice)) as MinPrice,
                    IFNULL(AVG(fb.Rating), 0) as AvgRating,
                    GROUP_CONCAT(
                        CONCAT_WS('|', s.SKUID, s.Attribute, s.OriginalPrice, IFNULL(s.PromotionPrice, 'NULL'), i.Stock, i.InventoryStatus)
                        SEPARATOR ';;'
                    ) as sku_details
                FROM PRODUCT p
                JOIN CATEGORY c ON p.CategoryID = c.CategoryID
                LEFT JOIN SKU s ON p.ProductID = s.ProductID
                LEFT JOIN INVENTORY i ON s.InventoryID = i.InventoryID
                LEFT JOIN FEEDBACK fb ON fb.SKUID = s.SKUID
                $whereSql
                GROUP BY p.ProductID, p.ProductName, p.Image, p.Ingredient, p.Flavour, p.Filter, c.CategoryName
                $havingSql
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($bind as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $self = $this;
        $products = array_map(function ($row) use ($self) {
            $skus = [];
            if (!empty($row['sku_details'])) {
                $skuItems = explode(';;', $row['sku_details']);
                foreach ($skuItems as $item) {
                    $parts = explode('|', $item);
                    if (count($parts) === 6) {
                        $skus[] = [
                            'skuId' => $parts[0],
                            'attribute' => $parts[1] . 'g',
                            'originalPrice' => (float) $parts[2],
                            'promotionPrice' => $parts[3] === 'NULL' ? null : (float) $parts[3],
                            'salePrice' => $parts[3] === 'NULL' ? (float) $parts[2] : (float) $parts[3],
                            'stock' => (int) $parts[4],
                            'status' => $parts[5]
                        ];
                    }
                }
            }

            return [
                'id' => $row['ProductID'],
                'name' => $row['ProductName'],
                'image' => $self->getProductThumbnailPath($row['Image']),
                'category' => $row['CategoryName'],
                'rating' => round($row['AvgRating'], 1),
                'ingredient' => $row['Ingredient'],
                'flavour' => $row['Flavour'],
                'filter' => $row['Filter'],
                'basePrice' => (float) $row['MinPrice'],
                'skus' => $skus,
                'totalStock' => array_sum(array_column($skus, 'stock'))
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));

        return [
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'products' => $products
        ];
    }
}