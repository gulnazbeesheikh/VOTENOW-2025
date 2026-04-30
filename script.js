// ============================================================
// script.js - Main JavaScript File
// Chart rendering, animations, vote confirmation
// (OTP removed — replaced with PIN-based login)
// ============================================================

// ----- Landing Page Auto-Redirect -----
function startRedirect(url, seconds) {
    let remaining = seconds;
    const countEl = document.getElementById('countdown');
    const interval = setInterval(function () {
        remaining--;
        if (countEl) countEl.textContent = remaining;
        if (remaining <= 0) {
            clearInterval(interval);
            window.location.href = url;
        }
    }, 1000);
}

// ----- Bar Chart for Results (uses Chart.js CDN) -----
function drawResultsChart(labels, data, totalVotes) {
    const ctx = document.getElementById('resultsChart').getContext('2d');

    const colors = [
        'rgba(26, 115, 232, 0.85)',
        'rgba(52, 168, 83, 0.85)',
        'rgba(251, 188, 4, 0.85)',
        'rgba(234, 67, 53, 0.85)',
        'rgba(103, 58, 183, 0.85)'
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Votes Received',
                data: data,
                backgroundColor: colors.slice(0, data.length),
                borderColor: colors.slice(0, data.length).map(c => c.replace('0.85', '1')),
                borderWidth: 1.5,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const votes = context.parsed.y;
                            const pct = totalVotes > 0
                                ? ((votes / totalVotes) * 100).toFixed(1) : 0;
                            return ' ' + votes + ' votes (' + pct + '%)';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1, precision: 0 },
                    title: { display: true, text: 'Number of Votes' }
                },
                x: {
                    title: { display: true, text: 'Candidates' }
                }
            }
        }
    });
}

// ----- Confirm before voting -----
function confirmVote(candidateName) {
    return confirm('Vote for ' + candidateName + '?\n\nThis cannot be undone.');
}

// ----- Animate vote progress bars on results page -----
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.vote-bar').forEach(function (bar) {
        const w = bar.getAttribute('data-width');
        setTimeout(function () { bar.style.width = w + '%'; }, 120);
    });
});
