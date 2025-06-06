<?php
require_once 'models/Pet.php';
require_once 'core/Helpers.php'; // Include Helpers for search functions

class PetController extends Controller {
    public function index() {
        $petModel = new Pet();
        
        // Server-side pagination and filtering
        $limit = 9; // Number of pets per page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Get filter parameters from GET request
        $query = isset($_GET['query']) ? trim($_GET['query']) : null;
        $type = isset($_GET['type']) ? trim($_GET['type']) : null;
        $gender = isset($_GET['gender']) ? trim($_GET['gender']) : null;
        $minAge = isset($_GET['minAge']) && $_GET['minAge'] !== '' ? (int)$_GET['minAge'] : null;
        $maxAge = isset($_GET['maxAge']) && $_GET['maxAge'] !== '' ? (int)$_GET['maxAge'] : null;
        
        error_log("PetController index - Parameters: query=$query, type=$type, gender=$gender, minAge=$minAge, maxAge=$maxAge");
        
        // Fetch paginated and filtered pets
        $pets = $petModel->getPaginated($limit, $offset, $type, $gender, null, $minAge, $maxAge, $query);
        $totalPets = $petModel->getTotalCount($type, $gender, null, $minAge, $maxAge, $query);
        
        error_log("PetController index - Total pets: $totalPets");
        
        // Calculate total pages
        $totalPages = ceil($totalPets / $limit);
        
        // Pass data to the view
        $this->view('pets/index', [
            'pets' => $pets,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'limit' => $limit,
            'filterQuery' => $query,
            'filterType' => $type,
            'filterGender' => $gender,
            'filterMinAge' => $minAge,
            'filterMaxAge' => $maxAge
        ]);
    }
    
    public function show($id) {
        $petModel = new Pet();
        $pet = $petModel->getById($id);
        
        if (!$pet) {
            $this->redirect('/pets');
            return;
        }
        
        $this->view('pets/show', [
            'pet' => $pet
        ]);
    }
    
    public function adopt() {
        if (!isLoggedIn()) {
            $this->redirect('/login');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $petId = $_POST['pet_id'];
            $userId = $_SESSION['user_id'];
            
            $petModel = new Pet();
            if ($petModel->adopt($petId, $userId)) {
                $_SESSION['success'] = 'Pet adopted successfully!';
                $this->redirect('/adopted-pets');
            } else {
                $_SESSION['error'] = 'Failed to adopt pet.';
                $this->redirect('/pet/' . $petId);
            }
        }
    }
    
    public function adoptedPets() {
        $petModel = new Pet();
        $adoptedPets = $petModel->getAdoptedPets();
        
        $this->view('pets/adopted', [
            'adoptedPets' => $adoptedPets
        ]);
    }
}
?>
