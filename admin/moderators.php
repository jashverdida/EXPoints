<?php
require_once __DIR__ . '/../config/supabase-session.php';
session_start();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>EXPoints • Admin Moderators</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="../Assets/css/index.css">
  <link rel="stylesheet" href="../Assets/css/admin.css">
  
  <style>
    /* Mobile Responsive Styles for Moderators Page */
    @media (max-width: 768px) {
      body {
        overflow-x: hidden;
        padding: 0.5rem;
      }
      
      .container-xl {
        padding: 0.5rem;
      }
      
      /* Topbar */
      .topbar {
        padding: 1rem;
        gap: 1rem;
        flex-wrap: wrap;
      }
      
      .lp-brand-img {
        height: 32px !important;
      }
      
      .search {
        order: 3;
        width: 100% !important;
        flex: 1 1 100%;
      }
      
      .search input {
        font-size: 1rem !important;
        padding: 1rem !important;
        min-height: 48px;
      }
      
      .search button {
        min-width: 48px;
        min-height: 48px;
      }
      
      .right {
        gap: 0.5rem;
      }
      
      .right .icon {
        min-width: 44px;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
      }
      
      /* Main Content */
      main {
        padding: 1rem 0.5rem !important;
      }
      
      /* Admin Card */
      .admin-card {
        padding: 1.25rem !important;
        margin-bottom: 1.5rem;
      }
      
      .section-title {
        font-size: 1.35rem !important;
        margin-bottom: 1.25rem !important;
      }
      
      /* Add Moderator Form */
      .add-mod {
        gap: 0.75rem !important;
        margin-bottom: 1.5rem;
      }
      
      .add-mod .col-12 {
        width: 100% !important;
        max-width: 100% !important;
        flex: 1 1 100%;
      }
      
      .add-mod .input-pill {
        font-size: 1rem !important;
        padding: 1rem !important;
        min-height: 48px;
      }
      
      .add-mod .btn-brand {
        width: 100%;
        padding: 1rem !important;
        font-size: 1rem !important;
        min-height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
      }
      
      .add-mod .btn-brand i {
        font-size: 1.1rem;
      }
      
      /* Table Responsive */
      .table-responsive {
        margin-top: 1.5rem !important;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
      }
      
      .admin-table {
        min-width: 100%;
        font-size: 0.9rem;
      }
      
      .admin-table thead th {
        font-size: 0.85rem !important;
        padding: 0.85rem 0.75rem !important;
        white-space: nowrap;
      }
      
      .admin-table tbody td {
        padding: 1rem 0.75rem !important;
        font-size: 0.9rem !important;
      }
      
      .admin-table tbody td:first-child {
        font-weight: 600;
      }
      
      /* Action Buttons in Table */
      .admin-table .btn {
        padding: 0.65rem 1rem !important;
        font-size: 0.85rem !important;
        min-height: 44px;
        white-space: nowrap;
      }
      
      .admin-table .btn i {
        font-size: 0.95rem;
      }
      
      /* Alternative: Card-based layout for very small screens */
      @supports (display: grid) {
        .admin-table {
          display: block;
        }
        
        .admin-table thead {
          display: none;
        }
        
        .admin-table tbody {
          display: grid;
          gap: 1rem;
        }
        
        .admin-table tr {
          display: grid;
          grid-template-columns: 1fr;
          gap: 0.5rem;
          padding: 1rem;
          background: rgba(59, 130, 246, 0.1);
          border: 1px solid rgba(59, 130, 246, 0.3);
          border-radius: 0.75rem;
        }
        
        .admin-table td {
          display: flex;
          flex-direction: column;
          gap: 0.25rem;
          padding: 0.5rem !important;
          border: none !important;
        }
        
        .admin-table td::before {
          content: attr(data-label);
          font-weight: 600;
          font-size: 0.75rem;
          color: rgba(255, 255, 255, 0.6);
          text-transform: uppercase;
          letter-spacing: 0.05em;
        }
        
        .admin-table td:nth-child(1)::before {
          content: "Username";
        }
        
        .admin-table td:nth-child(2)::before {
          content: "Email";
        }
        
        .admin-table td:nth-child(3)::before {
          content: "Since";
        }
        
        .admin-table td:nth-child(4)::before {
          content: "Actions";
        }
        
        .admin-table td:last-child {
          text-align: left !important;
        }
        
        .admin-table td:last-child .btn {
          width: 100%;
          justify-content: center;
        }
      }
    }
    
    /* Small Mobile Adjustments */
    @media (max-width: 480px) {
      .topbar {
        padding: 0.85rem;
      }
      
      .lp-brand-img {
        height: 28px !important;
      }
      
      .section-title {
        font-size: 1.2rem !important;
      }
      
      .admin-card {
        padding: 1rem !important;
      }
      
      .add-mod .input-pill {
        padding: 0.85rem !important;
      }
      
      .admin-table tr {
        padding: 0.85rem;
      }
    }
  </style>
</head>
<body>

  <div class="container-xl mt-3">
    <header class="topbar">
      <a href="../dashboard.php" class="lp-brand" aria-label="Dashboard">
        <img src="../Assets/img/EXPoints Logo.png" alt="+EXPoints" class="lp-brand-img">
      </a>
      <form class="search" role="search">
        <input type="text" placeholder="Search moderators" />
        <button class="icon" type="submit" aria-label="Search"><i class="bi bi-search"></i></button>
      </form>
      <div class="right">
        <a href="index.php" class="icon" title="Dashboard"><i class="bi bi-speedometer"></i></a>
        <a href="reporting.php" class="icon" title="Reporting"><i class="bi bi-flag"></i></a>
        <a class="icon" title="Moderators"><i class="bi bi-people-fill"></i></a>
      </div>
    </header>
  </div>

  <main class="container-xl py-4">
    <section class="admin-card">
      <h2 class="section-title">Moderators</h2>
      <form class="row g-2 add-mod">
        <div class="col-12 col-md-5">
          <input type="text" class="form-control input-pill" placeholder="@username" required />
        </div>
        <div class="col-12 col-md-5">
          <input type="email" class="form-control input-pill" placeholder="email@example.com" required />
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button class="btn btn-primary btn-brand" type="submit"><i class="bi bi-person-plus"></i> Add</button>
        </div>
      </form>

      <div class="table-responsive mt-3">
        <table class="table table-dark table-striped align-middle mb-0 admin-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Since</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>@mod-kai</td>
              <td>kai@example.com</td>
              <td>2024-06-01</td>
              <td class="text-end"><button class="btn btn-sm btn-outline-light"><i class="bi bi-person-dash"></i> Remove</button></td>
            </tr>
            <tr>
              <td>@mod-rin</td>
              <td>rin@example.com</td>
              <td>2024-07-21</td>
              <td class="text-end"><button class="btn btn-sm btn-outline-light"><i class="bi bi-person-dash"></i> Remove</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <script>
    document.querySelector('.add-mod')?.addEventListener('submit', (e) => {
      e.preventDefault();
      alert('Moderator added (demo).');
      e.target.reset();
    });
  </script>

</body>
</html>



