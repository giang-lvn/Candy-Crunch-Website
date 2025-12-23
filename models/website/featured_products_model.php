<?php
/**
 * Featured Products Model
 * Lấy danh sách sản phẩm nổi bật để hiển thị trên landing page
 */
class FeaturedProductsModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Helper function to extract thumbnail path from JSON image data
     */
    private function getProductThumbnailPath($imageData): string
    {
        if (empty($imageData))
            return '';

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

    /**
     * Lấy danh sách sản phẩm nổi bật
     * @param int $limit Số lượng sản phẩm cần lấy
     * @return array
     */
    public function getFeaturedProducts(int $limit = 10): array
    {
        $sql = "SELECT 
                    p.ProductID, 
                    p.ProductName, 
                    p.Description,
                    p.Image,
                    c.CategoryName,
                    IFNULL(AVG(fb.Rating), 0) as AvgRating,
                    MIN(s.SKUID) as FirstSKUID,
                    MIN(IFNULL(s.PromotionPrice, s.OriginalPrice)) as MinPrice
                FROM PRODUCT p
                JOIN CATEGORY c ON p.CategoryID = c.CategoryID
                LEFT JOIN SKU s ON p.ProductID = s.ProductID
                LEFT JOIN FEEDBACK fb ON fb.SKUID = s.SKUID
                WHERE p.ProductName IS NOT NULL AND p.ProductName != ''
                GROUP BY p.ProductID, p.ProductName, p.Description, p.Image, c.CategoryName
                ORDER BY AvgRating DESC, p.ProductID ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $self = $this;
        return array_map(function ($row) use ($self) {
            return [
                'id' => $row['ProductID'],
                'name' => $row['ProductName'],
                'description' => $row['Description'] ?? 'Delicious candy for everyone',
                'image' => $self->getProductThumbnailPath($row['Image']),
                'category' => $row['CategoryName'],
                'rating' => round($row['AvgRating'], 1),
                'skuId' => $row['FirstSKUID'],
                'price' => (float) $row['MinPrice']
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
