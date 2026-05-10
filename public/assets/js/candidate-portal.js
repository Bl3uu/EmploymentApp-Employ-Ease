// Global state
let currentJob = null;

// 1. Resume Upload UX (Fixed)
const resumeInput = document.querySelector('input[name="resume"]');
if (resumeInput) {
    resumeInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            const fileName = e.target.files[0].name;
            const labelSpan = document.querySelector('.text-blue-600');
            const container = document.querySelector('.bg-blue-50');
            
            labelSpan.innerText = "Selected: " + fileName;
            container.classList.replace('bg-blue-50', 'bg-green-50');
            container.classList.replace('border-blue-200', 'border-green-200');
        }
    });
}

// 2. Security: Logout (Fixed to use your Router)
function handleLogout() {
    if (confirm("Are you sure you want to log out?")) {
        // Since we have <base href>, we just call 'logout'
        window.location.href = "logout"; 
    }
}

// 3. Search Filter (Job Cards)
function filterJobs() {
    let input = document.getElementById('searchInput').value.toLowerCase();
    let cards = document.querySelectorAll('.job-card'); // Use querySelectorAll for consistency

    cards.forEach(card => {
        let title = card.querySelector('h3').innerText.toLowerCase();
        card.style.display = title.includes(input) ? 'flex' : 'none';
    });
}

// 4. Modal Controls
function openApplyModal(title, company, jobId) {
    const modalTitle = document.getElementById('applyJobTitle');
    const modalIdInput = document.getElementById('applyJobId');
    const modal = document.getElementById('applyModal');

    if (modalTitle) modalTitle.innerText = title + " @ " + company;
    if (modalIdInput) modalIdInput.value = jobId; 
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeApplyModal() {
    const modal = document.getElementById('applyModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

// NOTE: I removed submitApplication(event) to allow PHP to handle the POST request.