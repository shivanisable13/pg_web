<?php
$pageTitle = "Support Chat";
require_once '../includes/config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || !hasRole('owner')) redirect('/auth/login.php');

require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="glass-card p-0 overflow-hidden" style="height: 600px; display: flex; flex-direction: column;">
                <div class="bg-primary text-white p-3 d-flex align-items-center">
                    <div class="icon-box bg-white text-primary me-3" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">CampusStay Support</h5>
                        <span class="small opacity-75">Typically responds in 2 hours</span>
                    </div>
                </div>

                <div id="chat-messages" class="flex-grow-1 p-4 overflow-auto bg-light" style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png');">
                    <div class="d-flex justify-content-start mb-3">
                        <div class="p-3 rounded-4 bg-white shadow-sm max-w-75">
                            Hello! I am your CampusStay support assistant. How can I help you today?
                            <div class="text-end mt-1 text-muted" style="font-size: 10px;">System • Just now</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-white border-top">
                    <form id="support-form" class="d-flex gap-2">
                        <input type="text" class="form-control rounded-pill px-3" placeholder="Type your issue here...">
                        <button type="submit" class="btn btn-primary rounded-circle" style="width: 45px; height: 45px;">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
