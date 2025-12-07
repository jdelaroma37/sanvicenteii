<?php
session_start();
require_once 'config.php';

// ✅ Check if resident is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// ✅ Fetch resident info
$stmt = $pdo->prepare("SELECT * FROM residents WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$resident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$resident) {
    header("Location: ../index.php");
    exit;
}

// Helper function to safely read values
function displayValue($value, $fallback = 'N/A') {
    $trimmed = trim((string)$value);
    return $trimmed !== '' ? htmlspecialchars($trimmed) : $fallback;
}

// Normalize missing fields to avoid undefined index notices
$defaultFields = [
    'barangay' => '',
    'profile_complete' => 'No',
    'household_head' => '',
    'beneficiary_4ps' => '',
    'business_income_range' => '',
    'business_name' => '',
    'business_type' => '',
    'child_4ps_beneficiary' => '',
    'city_province' => '',
    'civil_status' => '',
    'contact_number' => '',
    'currently_studying' => '',
    'date_of_birth' => '',
    'disability_type' => '',
    'disaster_vulnerable' => '',
    'email' => '',
    'employer' => '',
    'evacuation_assistance' => '',
    'gender' => '',
    'grade_year_level' => '',
    'gsis_pensioner' => '',
    'highest_educ_attainment' => '',
    'home_number' => '',
    'income_category' => '',
    'ip_member' => '',
    'lactating_mother' => '',
    'livelihood_assistance' => '',
    'middle_name' => '',
    'monthly_income_range' => '',
    'monthly_pension' => '',
    'municipality' => '',
    'nationality' => '',
    'occupation' => '',
    'ofw_agency' => '',
    'ofw_country' => '',
    'ofw_household_member' => '',
    'ofw_job_position' => '',
    'other_cash_assistance' => '',
    'pension_type' => '',
    'philhealth_member' => '',
    'photo' => '',
    'place_of_birth' => '',
    'pregnant_woman' => '',
    'precinct_number' => '',
    'pwd' => '',
    'registered_farmer' => '',
    'registered_fisherfolk' => '',
    'registered_voter' => '',
    'religion' => '',
    'resident_since_birth' => '',
    'resident_type' => '',
    'school_name' => '',
    'senior_citizen' => '',
    'social_pension_beneficiary' => '',
    'solo_parent' => '',
    'sss_pensioner' => '',
    'street' => '',
    'student_supported_by' => '',
    'suffix' => '',
    'type_of_work' => '',
    'voter_in_barangay' => '',
    'work_department' => '',
    'years_of_residency' => '',
    'youth_member' => ''
];

foreach ($defaultFields as $key => $default) {
    if (!array_key_exists($key, $resident) || $resident[$key] === null) {
        $resident[$key] = $default;
    }
}

// Build a reliable photo source
$rawPhoto = trim($resident['photo']);
$resolvedPhoto = '';
if (!empty($rawPhoto)) {
    if (file_exists(__DIR__ . '/' . ltrim($rawPhoto, '/\\'))) {
        $resolvedPhoto = ltrim($rawPhoto, '/\\');
    } elseif (file_exists(__DIR__ . '/uploads/' . ltrim($rawPhoto, '/\\'))) {
        $resolvedPhoto = 'uploads/' . ltrim($rawPhoto, '/\\');
    }
}
$photoSrc = $resolvedPhoto ?: 'uploads/default.png';

$showCompleteProfileModal = ($resident['profile_complete'] === 'No');
$address_parts = array_filter([
    $resident['home_number'],
    $resident['street'],
    $resident['barangay'],
    $resident['municipality'],
    $resident['city_province']
], function ($part) {
    return trim((string)$part) !== '';
});

$formattedAddress = $address_parts ? htmlspecialchars(implode(', ', $address_parts)) : 'N/A';
$fullNameDisplay = displayValue($resident['first_name'] . ' ' . $resident['last_name']);

$stmtFamily = $pdo->prepare("SELECT * FROM family_members WHERE resident_id = ? ORDER BY created_at DESC");
$stmtFamily->execute([$_SESSION['user_id']]);
$familyMembers = $stmtFamily->fetchAll(PDO::FETCH_ASSOC);

// ✅ Handle profile photo update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $fileName = basename($_FILES['photo']['name']);
    $targetFile = $uploadDir . uniqid() . '_' . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
            $stmt = $pdo->prepare("UPDATE residents SET photo = ? WHERE id = ?");
            $stmt->execute([$targetFile, $_SESSION['user_id']]);
            header("Location: res_profile.php");
            exit;
        } else $error = "Error uploading file.";
    } else $error = "Invalid file type. Only JPG, JPEG, PNG, or GIF allowed.";
}

$full_name = htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resident Profile | Barangay San Vicente II</title>
  <link rel="stylesheet" href="res_profile.css">
</head>
<body>

<div id="completeProfileModal" class="modal">
  <div class="modal-content">
    <span class="close" id="closeCompleteProfile">&times;</span>
    <h2>Complete Your Profile</h2>
    <p>Your profile is incomplete. Please update your information to enjoy full access.</p>
    <button class="btn blue" onclick="openModal('addressModal')">Complete Now</button>
  </div>
</div>

<!-- NAVBAR -->
<nav>
  <div class="logo">
    <img src="images/logo.jpg" alt="logo">
    <h2>Barangay San Vicente II</h2>
  </div>

  <div class="menu-toggle" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>

  <div class="nav-links" id="navLinks">
    <a href="resident_dashboard.php">HOME</a>
    <a href="services.php">SERVICES</a>
    <a href="barangay.php">OUR BARANGAY</a>
    <a href="res_notification.php">NOTIFICATION</a>
    <a href="about.php">ABOUT</a>

    <div class="dropdown">
      <button class="dropbtn" onclick="toggleDropdown(this)">👤 <?php echo $full_name; ?> ▾</button>
      <div class="dropdown-menu">
        <a href="res_profile.php">VIEW PROFILE</a>
        <a href="../index.php">LOGOUT</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO SECTION -->
<div class="hero-section">
    <div class="hero-content">
        <div class="profile-photo" data-modal-target="photoModal">
            <img src="<?php echo htmlspecialchars($photoSrc); ?>" alt="Profile photo">
        </div>

        <div class="profile-info">
            <h1 class="hero-name"><?php echo htmlspecialchars($resident['first_name'] . ' ' . $resident['last_name']); ?></h1>
            <p class="hero-email"><?php echo htmlspecialchars($resident['email']); ?></p>
        </div>

        <div class="button-row">
            <button id="openAddFamily" class="btn blue">Add Family</button>
            <button class="btn gray" onclick="goToSummary()">View Request</button>
            <button type="button" class="btn outline" data-modal-target="photoModal">Change Photo</button>
        </div>
    </div>
</div>

<!-- ===== TABS ===== -->
<div class="tabs">
    <button class="tab active" data-target="all">All</button>
    <button class="tab" data-target="address">Residency</button>
    <button class="tab" data-target="basic">Personal</button>
    <button class="tab" data-target="status">Status</button>
    <button class="tab" data-target="govern">Government Benefits</button>
    <button class="tab" data-target="other">Other</button>
    <button class="tab" data-target="account">Account Info</button>
</div>

<!-- ===== CONTENT ===== -->
<div class="content">

    <!-- ALL SECTION -->
    <div class="tab-content active" id="all">
<h3>Address / Residency Information <span class="edit-icon" data-modal="addressModal">✎</span></h3>
        <ul class="info-list">
          <li><strong>Address: </strong><?php echo $formattedAddress; ?></li>
            <li><strong>Years of Residency: </strong> <?php echo displayValue($resident['years_of_residency']); ?></li>
            <li><strong>Resident Type: </strong> <?php echo displayValue($resident['resident_type']); ?></li>
            <li><strong>Resident since birth: </strong> <?php echo displayValue($resident['resident_since_birth']); ?></li>
        </ul>
        <hr>
        <br>

        <h3>Personal Information <span class="edit-icon" data-modal="personalInfoModal">✎</span></h3>
        <ul class="info-list">
          <li><strong><ion-icon name="male-female"></ion-icon> Fullname:</strong> <?php echo $fullNameDisplay; ?></li>
            <li><strong><ion-icon name="male-female"></ion-icon> Gender:</strong> <?php echo displayValue($resident['gender']); ?></li>
            <li><strong><ion-icon name="heart-half"></ion-icon> Civil Status:</strong> <?php echo displayValue($resident['civil_status']); ?></li>
            <li><strong><ion-icon name="calendar"></ion-icon> Date of Birth:</strong> <?php echo displayValue($resident['date_of_birth']); ?></li>
            <li><strong><ion-icon name="location"></ion-icon> Place of Birth:</strong> <?php echo displayValue($resident['place_of_birth']); ?></li>
            <li><strong>Religion:</strong> <?php echo displayValue($resident['religion']); ?></li>
            <li><strong><ion-icon name="flag"></ion-icon> Nationality:</strong> <?php echo displayValue($resident['nationality']); ?></li>
        </ul>
        <h3>Educational Status</h3>
        <ul class="info-list">
            <li><strong>Currently Studying?</strong> <?php echo displayValue($resident['currently_studying']); ?></li>
            <li><strong>Grade/Year Level: </strong> <?php echo displayValue($resident['grade_year_level']); ?></li>
            <li><strong>Highest Educational Attainment:</strong> <?php echo displayValue($resident['highest_educ_attainment']); ?></li>
            <li><strong>Name of School:</strong> <?php echo displayValue($resident['school_name']); ?></li>
        </ul>
        <hr>
        <br>

        <h3>Employment & Income Status <span class="edit-icon" data-modal="employmentModal">✎</span></h3>
<ul class="info-list">
  <li><strong>Source of Income (Main Category):</strong> <?php echo displayValue($resident['income_category']); ?></li>

  <!-- EMPLOYED -->
  <?php if($resident['income_category'] == 'Employed'): ?>
    <li><strong>Occupation / Job Title:</strong> <?php echo displayValue($resident['occupation']); ?></li>
    <li><strong>Work Department:</strong> <?php echo displayValue($resident['work_department']); ?></li>
    <li><strong>Company / Employer:</strong> <?php echo displayValue($resident['employer']); ?></li>
    <li><strong>Type of Work:</strong> <?php echo displayValue($resident['type_of_work']); ?></li>
    <li><strong>Monthly Income Range:</strong> <?php echo displayValue($resident['monthly_income_range']); ?></li>
  <?php endif; ?>

  <!-- SELF-EMPLOYED -->
  <?php if($resident['income_category'] == 'Self-Employed'): ?>
    <li><strong>Type of Business:</strong> <?php echo displayValue($resident['business_type']); ?></li>
    <li><strong>Business Name:</strong> <?php echo displayValue($resident['business_name']); ?></li>
    <li><strong>Income Range:</strong> <?php echo displayValue($resident['business_income_range']); ?></li>
  <?php endif; ?>

  <!-- STUDENT -->
  <?php if($resident['income_category'] == 'Student'): ?>
    <li><strong>School Name:</strong> <?php echo displayValue($resident['school_name']); ?></li>
    <li><strong>Grade / Year Level:</strong> <?php echo displayValue($resident['grade_year_level']); ?></li>
    <li><strong>Supported By:</strong> <?php echo displayValue($resident['student_supported_by']); ?></li>
  <?php endif; ?>

  <!-- PENSIONER -->
  <?php if($resident['income_category'] == 'Pensioner'): ?>
    <li><strong>Pension Type:</strong> <?php echo displayValue($resident['pension_type']); ?></li>
    <li><strong>Monthly Pension:</strong> <?php echo displayValue($resident['monthly_pension']); ?></li>
  <?php endif; ?>

  <!-- OFW -->
  <?php if($resident['income_category'] == 'OFW'): ?>
    <li><strong>Country of Work:</strong> <?php echo displayValue($resident['ofw_country']); ?></li>
    <li><strong>Job Position Abroad:</strong> <?php echo displayValue($resident['ofw_job_position']); ?></li>
    <li><strong>Work Agency:</strong> <?php echo displayValue($resident['ofw_agency']); ?></li>
  <?php endif; ?>
</ul>

<hr><br>

        <h3>Government Program & Beneficiaries <span class="edit-icon" data-modal="govProgramModal">✎</span></h3>
        <ul class="info-list">
          <li><strong>4Ps Beneficiary: </strong> <?php echo displayValue($resident['beneficiary_4ps']); ?></li>
            <li><strong>Social Pension Beneficiary: </strong> <?php echo displayValue($resident['social_pension_beneficiary']); ?></li>
            <li><strong>SSS Pensioner: </strong> <?php echo displayValue($resident['sss_pensioner']); ?></li>
            <li><strong>GSIS Pensioner: </strong> <?php echo displayValue($resident['gsis_pensioner']); ?></li>
            <li><strong>PhilHealth Member: </strong> <?php echo displayValue($resident['philhealth_member']); ?></li>
            <li><strong>Other Cash Assistance Recipient: </strong> <?php echo displayValue($resident['other_cash_assistance']); ?></li>
        </ul>

        <h3>Additional Optional Categories</h3>
        <ul class="info-list">
          <li><strong>4Ps Child Beneficiary: </strong> <?php echo displayValue($resident['child_4ps_beneficiary']); ?></li>
            <li><strong>Disaster-Vulnerable Person: </strong> <?php echo displayValue($resident['disaster_vulnerable']); ?></li>
            <li><strong>Evacuation Assistance Needed: </strong> <?php echo displayValue($resident['evacuation_assistance']); ?></li>
            <li><strong>Livelihood Assistance Beneficiary: </strong> <?php echo displayValue($resident['livelihood_assistance']); ?></li>
            <li><strong>Registered Farmer: </strong> <?php echo displayValue($resident['registered_farmer']); ?></li>
            <li><strong>Registered Fisherfolk: </strong> <?php echo displayValue($resident['registered_fisherfolk']); ?></li>
        </ul>
        <hr>
        <br>

        <h3>Other Information <span class="edit-icon" data-modal="otherInfoModal">✎</span></h3>
        <ul class="info-list">
          <li><strong>Registered Voter: </strong><?php echo displayValue($resident['registered_voter']); ?></li>
            <li><strong>Voter in this Barangay: </strong> <?php echo displayValue($resident['voter_in_barangay']); ?></li>
            <li><strong>Precint No.:</strong> <?php echo displayValue($resident['precinct_number']); ?></li>
        </ul>

        <h3>Social Category</h3>
        <ul class="info-list">
          <li><strong>Senior Citizen: </strong><?php echo displayValue($resident['senior_citizen']); ?></li>
            <li><strong>Person with Disability (PWD): </strong> <?php echo displayValue($resident['pwd']); ?> | <strong>Type: </strong> <?php echo displayValue($resident['disability_type']); ?></li>
            <li><strong>Solo Parent:</strong> <?php echo displayValue($resident['solo_parent']); ?></li>
            <li><strong>Youth Member (15–30 y/o): </strong><?php echo displayValue($resident['youth_member']); ?></li>
            <li><strong>Indigenous Peoples (IP) Member: </strong><?php echo displayValue($resident['ip_member']); ?></li>
            <li><strong>OFW Household Member: </strong> <?php echo displayValue($resident['ofw_household_member']); ?></li>
            <li><strong>Pregnant Woman:</strong> <?php echo displayValue($resident['pregnant_woman']); ?></li>
            <li><strong>Lactating Mother:</strong> <?php echo displayValue($resident['lactating_mother']); ?></li>
        </ul>
        <hr>
        <br>

        <h3>Account Information <span class="edit-icon" data-modal="accountInfoModal">✎</span></h3>
        <ul class="info-list">
          <li><strong>Fullname: </strong> <?php echo $fullNameDisplay; ?></li>
            <li><strong>Email: </strong> <?php echo displayValue($resident['email']); ?></li>
            <li><strong>Contact No.: </strong> <?php echo displayValue($resident['contact_number']); ?></li>
            <li><a href="#" data-modal-target="photoModal">Change Profile Photo</a></li>
            <li><a href="change_password.php">Change Password</a></li>
        </ul>
        <hr>
        <br>


<?php if(!empty($familyMembers)): ?>
    <h3>Family Members</h3>
    <ul class="info-list">
        <?php foreach($familyMembers as $family): ?>
            <li>
                <strong><?php echo htmlspecialchars($family['relationship']); ?>:</strong>
                <?php echo htmlspecialchars($family['first_name'] . ' ' . 
                    ($family['middle_name'] ? $family['middle_name'] . ' ' : '') . 
                    $family['last_name']); ?> 
                | Gender: <?php echo htmlspecialchars($family['gender']); ?>
                | DOB: <?php echo htmlspecialchars($family['date_of_birth']); ?>
                <?php if($family['contact_number']): ?> | Contact: <?php echo htmlspecialchars($family['contact_number']); ?><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <h3>Family Members</h3>
    <br>
    <p>No family members added yet...</p>
<?php endif; ?>








<!-- CHANGE PHOTO MODAL -->
<div id="photoModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <h2>Change Profile Photo</h2>
      <span class="close">&times;</span>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <?php if(isset($error)): ?>
        <div class="alert error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <div class="form-group full-width">
        <label for="photo">Upload new photo</label>
        <input type="file" name="photo" id="photo" accept="image/*" required>
        <small>Use JPG, JPEG, PNG, or GIF.</small>
      </div>
      <div class="form-actions">
        <button type="submit">Upload Photo</button>
      </div>
    </form>
  </div>
</div>

<!------------ 1. ADDRESS / RESIDENCY EDIT MODAL ---------------->
<div id="addressModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <h2>Edit Address / Residency Information</h2>
      <span class="close">&times;</span>
    </div>
    <form action="resident/update_address.php" method="POST">
      <div class="form-row">
        <div class="form-group">
          <label for="home_number">House Number</label>
          <input type="text" name="home_number" id="home_number" value="<?php echo htmlspecialchars($resident['home_number']); ?>">
        </div>
        <div class="form-group">
          <label for="street">Street</label>
          <input type="text" name="street" id="street" value="<?php echo htmlspecialchars($resident['street']); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="barangay">Barangay</label>
          <input type="text" name="barangay" id="barangay" value="San Vicente II" readonly>
        </div>
        <div class="form-group">
          <label for="municipality">Municipality</label>
          <input type="text" name="municipality" id="municipality" value="<?php echo htmlspecialchars($resident['municipality']); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="city_province">City / Province</label>
          <input type="text" name="city_province" id="city_province" value="<?php echo htmlspecialchars($resident['city_province']); ?>">
        </div>
        <div class="form-group">
          <label for="years_of_residency">Years of Residency</label>
          <input type="text" name="years_of_residency" id="years_of_residency" value="<?php echo htmlspecialchars($resident['years_of_residency']); ?>">
        </div>
      </div>
<br>
      <div class="form-group full-width">
        <label for="resident_type">Residency Type</label>
          <select name="resident_type" id="resident_type">
            <option value="">Select...</option>
            <option value="Permanent" <?php if($resident['resident_type']=='Permanent') echo 'selected'; ?>>Permanent</option>
            <option value="Transient" <?php if($resident['resident_type']=='Transient') echo 'selected'; ?>>Transient</option>
          </select>
</div>

      <div class="form-actions">
        <button type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!----------------2. PERSONAL INFORMATION EDIT MODAL ------------>
<div id="personalInfoModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <h2>Edit Personal Information</h2>
      <span class="close">&times;</span>
    </div>
    <form action="resident/update_personal.php" method="POST">
      <!-- FULLNAME -->
      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name *</label>
          <input type="text" name="first_name" id="first_name" value="<?php echo htmlspecialchars($resident['first_name']); ?>" required>
        </div>
        <div class="form-group">
          <label for="middle_name">Middle Name</label>
          <input type="text" name="middle_name" id="middle_name" value="<?php echo htmlspecialchars($resident['middle_name']); ?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="last_name">Last Name *</label>
          <input type="text" name="last_name" id="last_name" value="<?php echo htmlspecialchars($resident['last_name']); ?>" required>
        </div>
        <div class="form-group">
          <label for="suffix">Suffix</label>
          <input type="text" name="suffix" id="suffix" placeholder="Jr., Sr., III" value="<?php echo htmlspecialchars($resident['suffix']); ?>">
        </div>
      </div>

      <!-- PERSONAL DETAILS -->
      <div class="form-row">
        <div class="form-group">
          <label for="gender">Gender *</label>
          <select name="gender" id="gender" required>
            <option value="">Select...</option>
            <option value="Male" <?php if($resident['gender']=='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if($resident['gender']=='Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if($resident['gender']=='Other') echo 'selected'; ?>>Other</option>
          </select>
        </div>
        <div class="form-group">
          <label for="civil_status">Civil Status</label>
          <select name="civil_status" id="civil_status">
            <option value="">Select...</option>
            <option value="Single" <?php if($resident['civil_status']=='Single') echo 'selected'; ?>>Single</option>
            <option value="Married" <?php if($resident['civil_status']=='Married') echo 'selected'; ?>>Married</option>
            <option value="Widowed" <?php if($resident['civil_status']=='Widowed') echo 'selected'; ?>>Widowed</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="dob">Date of Birth</label>
          <input type="date" name="dob" id="dob" value="<?php echo htmlspecialchars($resident['date_of_birth']); ?>">
        </div>
        <div class="form-group">
          <label for="place_of_birth">Place of Birth</label>
          <input type="text" name="place_of_birth" id="place_of_birth" value="<?php echo htmlspecialchars($resident['place_of_birth']); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="religion">Religion</label>
          <input type="text" name="religion" id="religion" value="<?php echo htmlspecialchars($resident['religion']); ?>">
        </div>
        <div class="form-group">
          <label for="nationality">Nationality</label>
          <input type="text" name="nationality" id="nationality" value="<?php echo htmlspecialchars($resident['nationality']); ?>">
        </div>
      </div>

      <!-- EDUCATIONAL STATUS -->
      <div class="form-row">
        <div class="form-group">
          <label for="currently_studying">Currently Studying?</label>
          <input type="text" name="currently_studying" id="currently_studying" value="<?php echo htmlspecialchars($resident['currently_studying']); ?>">
        </div>
        <div class="form-group">
          <label for="grade_year_level">Grade/Year Level</label>
          <input type="text" name="grade_year_level" id="grade_year_level" value="<?php echo htmlspecialchars($resident['grade_year_level']); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="highest_educ_attainment">Highest Educational Attainment</label>
          <input type="text" name="highest_educ_attainment" id="highest_educ_attainment" value="<?php echo htmlspecialchars($resident['highest_educ_attainment']); ?>">
        </div>
        <div class="form-group">
          <label for="school_name">Name of School</label>
          <input type="text" name="school_name" id="school_name" value="<?php echo htmlspecialchars($resident['school_name']); ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit">Save</button>
      </div>
    </form>
  </div>
</div>


<!------------------3. EMPLOYMENT------------------------------->
<div id="employmentModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <br>
      <h2>Edit Employment & Income Status</h2>
      <span class="close">&times;</span>
    </div>

    <form action="resident/update_employment.php" method="POST">
      <br>

      <!-- MAIN CATEGORY -->
      <div class="form-group full-width">
        <label for="income_category">Source of Income (Main Category)</label>
        <select name="income_category" id="income_category" required>
          <option value="">Select...</option>
          <option value="Employed"        <?php if($resident['income_category']=='Employed') echo 'selected'; ?>>Employed</option>
          <option value="Self-Employed"  <?php if($resident['income_category']=='Self-Employed') echo 'selected'; ?>>Self-Employed</option>
          <option value="Unemployed"     <?php if($resident['income_category']=='Unemployed') echo 'selected'; ?>>Unemployed</option>
          <option value="Student"        <?php if($resident['income_category']=='Student') echo 'selected'; ?>>Student</option>
          <option value="Pensioner"      <?php if($resident['income_category']=='Pensioner') echo 'selected'; ?>>Pensioner</option>
          <option value="OFW"            <?php if($resident['income_category']=='OFW') echo 'selected'; ?>>OFW</option>
          <option value="Dependent / No Income" <?php if($resident['income_category']=='Dependent / No Income') echo 'selected'; ?>>Dependent / No Income</option>
        </select>
      </div>
      <br>

      <!-- EMPLOYED SECTION -->
      <div id="employedSection" class="conditional-section">
        <h3>Employment Details</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Occupation / Job Title</label>
            <input type="text" name="occupation" value="<?php echo htmlspecialchars($resident['occupation']); ?>">
          </div>
          <div class="form-group">
            <label>Work Department</label>
            <input type="text" name="work_department" value="<?php echo htmlspecialchars($resident['work_department']); ?>">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Company / Employer</label>
            <input type="text" name="employer" value="<?php echo htmlspecialchars($resident['employer']); ?>">
          </div>
          <div class="form-group">
            <label>Type of Work</label>
            <select name="work_type">
              <option value="">Select...</option>
              <option value="Private"     <?php if($resident['type_of_work']=='Private') echo 'selected'; ?>>Private</option>
              <option value="Government"  <?php if($resident['type_of_work']=='Government') echo 'selected'; ?>>Government</option>
              <option value="Overseas"    <?php if($resident['type_of_work']=='Overseas') echo 'selected'; ?>>Overseas</option>
            </select>
          </div>
        </div>

        <div class="form-group full-width">
          <label>Monthly Income Range</label>
          <input type="text" name="monthly_income_range" value="<?php echo htmlspecialchars($resident['monthly_income_range']); ?>">
        </div>
      </div>
      <br>

      <!-- SELF-EMPLOYED SECTION -->
      <div id="selfEmployedSection" class="conditional-section">
        <h3>Self-Employed Details</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Type of Business</label>
            <input type="text" name="business_type" value="<?php echo htmlspecialchars($resident['business_type']); ?>">
          </div>
          <div class="form-group">
            <label>Business Name</label>
            <input type="text" name="business_name" value="<?php echo htmlspecialchars($resident['business_name']); ?>">
          </div>
        </div>

        <div class="form-group full-width">
          <label>Income Range</label>
          <input type="text" name="business_income_range" value="<?php echo htmlspecialchars($resident['business_income_range']); ?>">
        </div>
      </div>
      <br>

      <!-- STUDENT SECTION -->
      <div id="studentSection" class="conditional-section">
        <h3>Student Details</h3>
        <div class="form-row">
          <div class="form-group">
            <label>School Name</label>
            <input type="text" name="school_name" value="<?php echo htmlspecialchars($resident['school_name']); ?>">
          </div>
          <div class="form-group">
            <label>Grade / Year Level</label>
            <input type="text" name="grade_year_level" value="<?php echo htmlspecialchars($resident['grade_year_level']); ?>">
          </div>
        </div>

        <div class="form-group full-width">
          <label>Supported By</label>
          <input type="text" name="student_supported_by" value="<?php echo htmlspecialchars($resident['student_supported_by']); ?>">
        </div>
      </div>
      <br>

      <!-- PENSIONER SECTION -->
      <div id="pensionerSection" class="conditional-section">
        <label>Pension Type</label>
            <select name="pension_type">
              <option value="">Select...</option>
              <option value="GSIS"     <?php if($resident['pension_type']=='GSIS') echo 'selected'; ?>>GSIS</option>
              <option value="SSS"  <?php if($resident['pension_type']=='SSS') echo 'selected'; ?>>SSS</option>
              <option value="Private"    <?php if($resident['pension_type']=='Private') echo 'selected'; ?>>Private</option>
            </select>
      </div>
      <br>

      <!-- OFW SECTION -->
      <div id="ofwSection" class="conditional-section">
        <h3>OFW Details</h3>
        <div class="form-row">
          <div class="form-group">
            <label>Country of Work</label>
            <input type="text" name="ofw_country" value="<?php echo htmlspecialchars($resident['ofw_country']); ?>">
          </div>
          <div class="form-group">
            <label>Job Position Abroad</label>
            <input type="text" name="ofw_job_position" value="<?php echo htmlspecialchars($resident['ofw_job_position']); ?>">
          </div>
        </div>

        <div class="form-group full-width">
          <label>Work Agency (Optional)</label>
          <input type="text" name="ofw_agency" value="<?php echo htmlspecialchars($resident['ofw_agency']); ?>">
        </div>
      </div>

      <div class="form-actions">
        <button type="submit">Save</button>
      </div>

    </form>
  </div>
</div>

<!-----------4. GOVERNMENT PROGRAM MODAL ------------------------>
<div id="govProgramModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <br>
      <h2>Edit Government Program and Beneficiaries</h2>
      <span class="close">&times;</span>
    </div>
    <form action="resident/update_government_program.php" method="POST">

      <div class="form-group">
        <label>4Ps Beneficiary</label>
        <select name="beneficiary_4ps">
          <option value="Yes" <?php if($resident['beneficiary_4ps']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['beneficiary_4ps']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Social Pension Beneficiary</label>
        <select name="social_pension_beneficiary">
          <option value="Yes" <?php if($resident['social_pension_beneficiary']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['social_pension_beneficiary']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>SSS Pensioner</label>
        <select name="sss_pensioner">
          <option value="Yes" <?php if($resident['sss_pensioner']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['sss_pensioner']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>GSIS Pensioner</label>
        <select name="gsis_pensioner">
          <option value="Yes" <?php if($resident['gsis_pensioner']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['gsis_pensioner']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>PhilHealth Member</label>
        <select name="philhealth_member">
          <option value="Yes" <?php if($resident['philhealth_member']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['philhealth_member']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Other Cash Assistance Recipient</label>
        <input type="text" name="other_cash_assistance"
          value="<?php echo htmlspecialchars($resident['other_cash_assistance']); ?>">
      </div>

      <hr>
      <h3>Additional Optional Categories</h3>

      <div class="form-group">
        <label>4Ps Child Beneficiary</label>
        <select name="child_4ps_beneficiary">
          <option value="Yes" <?php if($resident['child_4ps_beneficiary']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['child_4ps_beneficiary']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Disaster-Vulnerable Person</label>
        <select name="disaster_vulnerable">
          <option value="Yes" <?php if($resident['disaster_vulnerable']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['disaster_vulnerable']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Evacuation Assistance Needed</label>
        <select name="evacuation_assistance">
          <option value="Yes" <?php if($resident['evacuation_assistance']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['evacuation_assistance']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Livelihood Assistance Beneficiary</label>
        <select name="livelihood_assistance">
          <option value="Yes" <?php if($resident['livelihood_assistance']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['livelihood_assistance']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Registered Farmer</label>
        <select name="registered_farmer">
          <option value="Yes" <?php if($resident['registered_farmer']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['registered_farmer']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Registered Fisherfolk</label>
        <select name="registered_fisherfolk">
          <option value="Yes" <?php if($resident['registered_fisherfolk']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['registered_fisherfolk']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <button type="submit" class="save-btn">Save Changes</button>

    </form>
  </div>
</div>


<!--------------5. OTHER INFORMATION MODAL ---------------------->
<div id="otherInfoModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <br>
      <h2>Edit Other Information</h2>
      <span class="close">&times;</span>
    </div>

    <form action="resident/update_other_information.php" method="POST">

      <!-- ===================== -->
      <!--   OTHER INFORMATION   -->
      <!-- ===================== -->
      <div class="form-group">
        <label>Registered Voter</label>
        <select name="registered_voter">
          <option value="Yes" <?php if($resident['registered_voter']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['registered_voter']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Voter in this Barangay</label>
        <select name="voter_in_barangay">
          <option value="Yes" <?php if($resident['voter_in_barangay']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['voter_in_barangay']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Precinct Number</label>
        <input type="text" name="precinct_number"
               value="<?php echo htmlspecialchars($resident['precinct_number']); ?>">
      </div>

      <hr>
      <h3>Social Category</h3>

      <!-- ===================== -->
      <!--    SOCIAL CATEGORY    -->
      <!-- ===================== -->

      <div class="form-group">
        <label>Senior Citizen</label>
        <select name="senior_citizen">
          <option value="Yes" <?php if($resident['senior_citizen']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['senior_citizen']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Person With Disability (PWD)</label>
        <select name="pwd">
          <option value="Yes" <?php if($resident['pwd']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['pwd']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Type of Disability</label>
        <input type="text" name="disability_type"
               value="<?php echo htmlspecialchars($resident['disability_type']); ?>">
      </div>

      <div class="form-group">
        <label>Solo Parent</label>
        <select name="solo_parent">
          <option value="Yes" <?php if($resident['solo_parent']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['solo_parent']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Youth Member (15–30 y/o)</label>
        <select name="youth_member">
          <option value="Yes" <?php if($resident['youth_member']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['youth_member']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Indigenous Peoples (IP) Member</label>
        <select name="ip_member">
          <option value="Yes" <?php if($resident['ip_member']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['ip_member']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>OFW Household Member</label>
        <select name="ofw_household_member">
          <option value="Yes" <?php if($resident['ofw_household_member']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['ofw_household_member']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Pregnant Woman</label>
        <select name="pregnant_woman">
          <option value="Yes" <?php if($resident['pregnant_woman']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['pregnant_woman']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <div class="form-group">
        <label>Lactating Mother</label>
        <select name="lactating_mother">
          <option value="Yes" <?php if($resident['lactating_mother']=='Yes') echo 'selected'; ?>>Yes</option>
          <option value="No"  <?php if($resident['lactating_mother']=='No') echo 'selected'; ?>>No</option>
        </select>
      </div>

      <button type="submit" class="save-btn">Save Changes</button>

    </form>
  </div>
</div>

<!------------------- ACCOUNT INFORMATION MODAL ----------------->
<div id="accountInfoModal" class="modal">
  <div class="modal-content modal-form">
    <div class="modal-header">
      <br>
      <h2>Edit Account Information</h2>
      <span class="close">&times;</span>
    </div>

    <form action="resident/update_account.php" method="POST">

      <!-- FULL NAME -->
      <div class="form-row">
        <div class="form-group">
          <label for="first_name">First Name</label>
          <input type="text" id="first_name" name="first_name"
                 value="<?php echo htmlspecialchars($resident['first_name']); ?>" required>
        </div>

        <div class="form-group">
          <label for="middle_name">Middle Name</label>
          <input type="text" id="middle_name" name="middle_name"
                 value="<?php echo htmlspecialchars($resident['middle_name']); ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="last_name">Last Name</label>
          <input type="text" id="last_name" name="last_name"
                 value="<?php echo htmlspecialchars($resident['last_name']); ?>" required>
        </div>

        <div class="form-group">
          <label for="suffix">Suffix</label>
          <input type="text" id="suffix" name="suffix"
                 value="<?php echo htmlspecialchars($resident['suffix']); ?>">
        </div>
      </div>

      <!-- EMAIL -->
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email"
               value="<?php echo htmlspecialchars($resident['email']); ?>">
      </div>

      <!-- CONTACT NUMBER -->
      <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input type="text" id="contact_number" name="contact_number"
               value="<?php echo htmlspecialchars($resident['contact_number']); ?>">
      </div>
      <br>

      <!-- CHANGE PASSWORD LINK -->
      <div class="form-group">
        <label>Password</label>
        <a href="change_password.php" class="change-password-link">Change Password</a>
      </div>
      <br>

      <button type="submit" class="save-btn">Save Changes</button>
    </form>
  </div>
</div>

































<script src="res_profile.js"></script>
</body>
</html>