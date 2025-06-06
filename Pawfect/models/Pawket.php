<?php
class Pawket {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function addToPawket($userId, $petId) {
        // Check if pet is already in pawket
        $stmt = $this->pdo->prepare("SELECT * FROM pawket WHERE user_id = ? AND pet_id = ?");
        $stmt->execute([$userId, $petId]);
        if ($stmt->fetch()) {
            return false; // Pet already in pawket
        }

        // Check if pet is available
        $stmt = $this->pdo->prepare("SELECT * FROM pets WHERE id = ? AND is_adopted = FALSE");
        $stmt->execute([$petId]);
        if (!$stmt->fetch()) {
            return false; // Pet not available
        }

        // Add to pawket
        $stmt = $this->pdo->prepare("INSERT INTO pawket (user_id, pet_id) VALUES (?, ?)");
        return $stmt->execute([$userId, $petId]);
    }

    public function removeFromPawket($userId, $petId) {
        $stmt = $this->pdo->prepare("DELETE FROM pawket WHERE user_id = ? AND pet_id = ?");
        return $stmt->execute([$userId, $petId]);
    }

    public function getPawketItems($userId) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, paw.id as pawket_id 
            FROM pawket paw 
            JOIN pets p ON paw.pet_id = p.id 
            WHERE paw.user_id = ?
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getPawketTotal($userId) {
        $stmt = $this->pdo->prepare("
            SELECT SUM(p.price) as total 
            FROM pawket paw 
            JOIN pets p ON paw.pet_id = p.id 
            WHERE paw.user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function clearPawket($userId) {
        $stmt = $this->pdo->prepare("DELETE FROM pawket WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }

    public function createOrder($userId, $totalAmount) {
        try {
            $this->pdo->beginTransaction();

            // Create order
            $stmt = $this->pdo->prepare("
                INSERT INTO pet_orders (user_id, total_amount) 
                VALUES (?, ?)
            ");
            $stmt->execute([$userId, $totalAmount]);
            $orderId = $this->pdo->lastInsertId();

            // Get pawket items
            $pawketItems = $this->getPawketItems($userId);

            // Create order items and mark pets as adopted
            foreach ($pawketItems as $item) {
                // Add to order items
                $stmt = $this->pdo->prepare("
                    INSERT INTO pet_order_items (order_id, pet_id, price) 
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$orderId, $item['id'], $item['price']]);

                // Mark pet as adopted
                $stmt = $this->pdo->prepare("
                    UPDATE pets 
                    SET is_adopted = TRUE, adopted_by_user_id = ? 
                    WHERE id = ?
                ");
                $stmt->execute([$userId, $item['id']]);
            }

            // Clear pawket
            $this->clearPawket($userId);

            $this->pdo->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function getItemCount($userId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM pawket WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }

    public function isInPawket($userId, $petId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM pawket WHERE user_id = ? AND pet_id = ?");
        $stmt->execute([$userId, $petId]);
        $result = $stmt->fetch();
        return ($result['count'] ?? 0) > 0;
    }
} 