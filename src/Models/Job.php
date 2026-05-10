<?php
class Job {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Fetch all jobs from the database
     */
    public function getAllJobs() {
        $query = "SELECT *
                  FROM jobs
                  WHERE status = 'Active'
                  ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveJobsFiltered($search = '', $location = '', $type = '', $sort = 'newest') {
        $query = "SELECT * FROM jobs WHERE status = 'Active'";
        $params = [];

        if (!empty($search)) {
            $query .= " AND (title LIKE :search OR company LIKE :search OR description LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($location)) {
            $query .= " AND location = :location";
            $params[':location'] = $location;
        }

        if (!empty($type)) {
            $query .= " AND type = :type";
            $params[':type'] = $type;
        }

        switch ($sort) {
            case 'oldest':
                $query .= " ORDER BY created_at ASC";
                break;
            case 'most_applicants':
                $query = "SELECT j.*, COUNT(a.id) AS applicant_count FROM jobs j LEFT JOIN applications a ON j.id = a.job_id WHERE j.status = 'Active'";
                if (!empty($search)) {
                    $query .= " AND (j.title LIKE :search OR j.company LIKE :search OR j.description LIKE :search)";
                }
                if (!empty($location)) {
                    $query .= " AND j.location = :location";
                }
                if (!empty($type)) {
                    $query .= " AND j.type = :type";
                }
                $query .= " GROUP BY j.id ORDER BY applicant_count DESC, j.created_at DESC";
                break;
            case 'least_applicants':
                $query = "SELECT j.*, COUNT(a.id) AS applicant_count FROM jobs j LEFT JOIN applications a ON j.id = a.job_id WHERE j.status = 'Active'";
                if (!empty($search)) {
                    $query .= " AND (j.title LIKE :search OR j.company LIKE :search OR j.description LIKE :search)";
                }
                if (!empty($location)) {
                    $query .= " AND j.location = :location";
                }
                if (!empty($type)) {
                    $query .= " AND j.type = :type";
                }
                $query .= " GROUP BY j.id ORDER BY applicant_count ASC, j.created_at DESC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY created_at DESC";
                break;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUniqueLocationsForActiveJobs() {
        $query = "SELECT DISTINCT location FROM jobs WHERE status = 'Active' AND location IS NOT NULL ORDER BY location ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'location');
    }

    public function getUniqueTypesForActiveJobs() {
        $query = "SELECT DISTINCT type FROM jobs WHERE status = 'Active' AND type IS NOT NULL ORDER BY type ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'type');
    }

    /**
     * Get details for a specific job
     */
    public function getJobById($id) {
        $query = "SELECT * FROM jobs WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * In your Job.class.php (or wherever you fetch recruiter jobs)
    * This uses a LEFT JOIN to count applications for each job.
    */
    public function getJobsByRecruiter($recruiter_id) {
        $query = "SELECT j.*, 
                COUNT(a.id) AS applicant_count 
                FROM jobs j
                LEFT JOIN applications a ON j.id = a.job_id
                WHERE j.recruiter_id = :recruiter_id
                GROUP BY j.id
                ORDER BY j.created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':recruiter_id' => $recruiter_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Inside class Job ...

    public function getJobDescription($id) {
        $query = "SELECT description FROM jobs WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['description'] ?? "";
    }

    /**
     * Create a new job listing
     */
    public function createJob($data) {
        $query = "INSERT INTO jobs (recruiter_id, title, company, location, type, description, category, status, max_applicants) 
                VALUES (:recruiter_id, :title, :company, :location, :type, :description, :category, :status, :max_applicants)";
        
        // We must ensure the array only contains keys that exist in the query
        $params = [
            ':recruiter_id'   => $data['recruiter_id'],
            ':title'          => $data['title'],
            ':company'        => $data['company'],
            ':location'       => $data['location'],
            ':type'           => $data['type'],
            ':description'    => $data['description'],
            ':category'       => $data['category'],
            ':status'         => $data['status'],
            ':max_applicants' => $data['max_applicants']
        ];

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Update an existing job listing
     */
    public function updateJob($id, $data) {
        $query = "UPDATE jobs SET 
                title = :title, 
                company = :company, 
                location = :location, 
                type = :type, 
                description = :description, 
                category = :category, 
                status = :status, 
                max_applicants = :max_applicants 
                WHERE id = :id";

        $params = [
            ':id'             => (int)$id,
            ':title'          => $data['title'],
            ':company'        => $data['company'],
            ':location'       => $data['location'],
            ':type'           => $data['type'],
            ':description'    => $data['description'],
            ':category'       => $data['category'],
            ':status'         => $data['status'],
            ':max_applicants' => $data['max_applicants']
        ];

        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    /**
     * Get jobs by recruiter with search, filters, and sorting
     */
    public function getJobsByRecruiterFiltered($recruiter_id, $search = '', $location = '', $type = '', $status = '', $sort = 'newest') {
        $recruiter_id = (int)$recruiter_id;

        $query = "SELECT j.*,
                COUNT(a.id) AS applicant_count
            FROM jobs j
            LEFT JOIN applications a ON j.id = a.job_id
            WHERE j.recruiter_id = :recruiter_id";

        $params = [':recruiter_id' => $recruiter_id];

        if ($search !== '') {
            $query .= " AND (j.title LIKE :search OR j.company LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if ($location !== '') {
            $query .= " AND j.location = :location";
            $params[':location'] = $location;
        }

        if ($type !== '') {
            $query .= " AND j.type = :type";
            $params[':type'] = $type;
        }

        if ($status !== '') {
            $query .= " AND j.status = :status";
            $params[':status'] = $status;
        }

        $query .= " GROUP BY j.id";

        switch ($sort) {
            case 'oldest':
                $query .= " ORDER BY j.created_at ASC";
                break;
            case 'most_applicants':
                $query .= " ORDER BY applicant_count DESC, j.created_at DESC";
                break;
            case 'least_applicants':
                $query .= " ORDER BY applicant_count ASC, j.created_at DESC";
                break;
            case 'newest':
            default:
                $query .= " ORDER BY j.created_at DESC";
                break;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unique locations for filter dropdown
     */
    public function getUniqueLocations($recruiter_id) {
        $query = "SELECT DISTINCT location FROM jobs WHERE recruiter_id = :recruiter_id AND location IS NOT NULL ORDER BY location ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':recruiter_id' => $recruiter_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'location');
    }

    /**
     * Get unique job types for filter dropdown
     */
    public function getUniqueTypes($recruiter_id) {
        $query = "SELECT DISTINCT type FROM jobs WHERE recruiter_id = :recruiter_id AND type IS NOT NULL ORDER BY type ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':recruiter_id' => $recruiter_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'type');
    }

    /**
     * Toggle job status to 'Closed'
     */
    public function closeJob($id) {
        $query = "UPDATE jobs SET status = 'Closed' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>