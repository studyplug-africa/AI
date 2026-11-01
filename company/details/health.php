 <?php
    session_start();

    if (!isset($_SESSION["admin_name"]) || !isset($_SESSION["company_name"])) {
        header("Location: ../login.php");
        exit();
    }

    $company_id = $_SESSION["company_id"];
    $company_name = $_SESSION["company_name"];
    $admin_name = $_SESSION["admin_name"];
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Health Score | OpsInsyts</title>
     <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
     <style>
         * {
             margin: 0;
             padding: 0;
             box-sizing: border-box;
         }

         body {
             font-family: 'Inter', sans-serif;
             background: white;
             padding: 2px;
         }

         :root {
             --primary: #2373BA;
             --primary-light: #0c96D4;
             --dark: #464646;
             --gray: #989798;
             --border: #e2e8f0;
             --critical: #dc2626;
             --warning: #f59e0b;
             --success: #10b981;
             --info: #3b82f6;
         }

         .page-header {
             margin-bottom: 32px;
         }

         .page-header h1 {
             font-size: 28px;
             font-weight: 700;
             color: var(--dark);
             display: flex;
             align-items: center;
             gap: 12px;
         }

         .page-header h1 i {
             color: var(--primary);
         }

         .page-header p {
             color: var(--gray);
             margin-top: 8px;
         }

         /* Health Score Main Card */
         .health-main {
             background: linear-gradient(rgba(35, 115, 186, 0.85), rgba(35, 115, 186, 0.85)), url('https://www.transparenttextures.com/patterns/carbon-fibre.png');
             background-size: cover, auto;
             background-position: center;
             background-repeat: no-repeat, repeat;
             border-radius: 5px;
             border: 1px solid var(--border);
             padding: 40px;
             margin-bottom: 32px;
             text-align: center;
             color: white;
         }

         .score-ring-container {
             position: relative;
             width: 220px;
             height: 220px;
             margin: 0 auto 24px;
         }

         .score-ring {
             width: 100%;
             height: 100%;
         }

         .score-number {
             position: absolute;
             top: 50%;
             left: 50%;
             transform: translate(-50%, -50%);
             font-size: 56px;
             font-weight: 800;
         }

         .score-number span {
             font-size: 24px;
             font-weight: 400;
         }

         .score-status {
             font-size: 20px;
             font-weight: 700;
             margin-bottom: 8px;
         }

         .score-message {
             font-size: 14px;
             color: var(--warning);
             max-width: 500px;
             margin: 0 auto;
         }

         /* Factors Grid */
         .factors-section {
             margin-bottom: 32px;
         }

         .section-header {
             margin-bottom: 20px;
         }

         .section-header h2 {
             font-size: 18px;
             font-weight: 700;
             color: var(--dark);
             display: flex;
             align-items: center;
             gap: 8px;
         }

         .section-header h2 i {
             color: var(--primary);
         }

         .factors-grid {
             display: grid;
             grid-template-columns: repeat(5, 1fr);
             gap: 20px;
         }

         .factor-card {
             background: white;
             border-radius: 5px;
             border: 1px solid var(--border);
             padding: 20px;
             text-align: center;
             transition: all 0.2s;
         }

         .factor-card:hover {
             transform: translateY(-2px);
             box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
         }

         .factor-icon {
             width: 48px;
             height: 48px;
             border-radius: 50%;
             display: flex;
             align-items: center;
             justify-content: center;
             margin: 0 auto 16px;
         }

         .factor-icon.cash {
             background: #eef4fc;
             color: var(--primary);
         }

         .factor-icon.overdue {
             background: #fee2e2;
             color: var(--critical);
         }

         .factor-icon.expense {
             background: #fed7aa;
             color: var(--warning);
         }

         .factor-icon.runway {
             background: #d1fae5;
             color: var(--success);
         }

         .factor-icon.compliance {
             background: #dbeafe;
             color: var(--info);
         }

         .factor-icon i {
             font-size: 24px;
         }

         .factor-name {
             font-size: 14px;
             font-weight: 600;
             color: var(--dark);
             margin-bottom: 8px;
         }

         .factor-value {
             font-size: 20px;
             font-weight: 800;
             color: var(--dark);
         }

         .factor-points {
             font-size: 12px;
             color: var(--gray);
             margin-top: 8px;
         }

         .factor-progress {
             margin-top: 12px;
             height: 4px;
             background: var(--border);
             border-radius: 2px;
             overflow: hidden;
         }

         .factor-progress-fill {
             height: 100%;
             border-radius: 2px;
             transition: width 0.3s;
         }

         /* Weight Distribution */
         .weight-section {
             background: white;
             border-radius: 5px;
             border: 1px solid var(--border);
             padding: 24px;
             margin-bottom: 32px;
         }

         .weight-header {
             margin-bottom: 20px;
         }

         .weight-header h3 {
             font-size: 16px;
             font-weight: 600;
             color: var(--dark);
             display: flex;
             align-items: center;
             gap: 8px;
         }

         .weight-bars {
             display: flex;
             gap: 16px;
             flex-wrap: wrap;
         }

         .weight-item {
             flex: 1;
             min-width: 120px;
         }

         .weight-label {
             font-size: 12px;
             color: var(--gray);
             margin-bottom: 8px;
             display: flex;
             justify-content: space-between;
         }

         .weight-bar {
             height: 8px;
             background: var(--border);
             border-radius: 4px;
             overflow: hidden;
         }

         .weight-fill {
             height: 100%;
             border-radius: 4px;
         }

         .weight-fill.cash {
             background: var(--primary);
             width: 25%;
         }

         .weight-fill.overdue {
             background: var(--critical);
             width: 25%;
         }

         .weight-fill.expense {
             background: var(--warning);
             width: 20%;
         }

         .weight-fill.runway {
             background: var(--success);
             width: 25%;
         }

         .weight-fill.compliance {
             background: var(--info);
             width: 5%;
         }

         /* Recommendations */
         .recommendations-section {
             background: #f8fafc;
             border-radius: 5px;
             padding: 24px;
         }

         .recommendations-header {
             margin-bottom: 20px;
         }

         .recommendations-header h3 {
             font-size: 16px;
             font-weight: 600;
             color: var(--dark);
             display: flex;
             align-items: center;
             gap: 8px;
         }

         .recommendations-list {
             list-style: none;
         }

         .recommendations-list li {
             padding: 12px 0;
             border-bottom: 1px solid var(--border);
             display: flex;
             align-items: center;
             gap: 12px;
         }

         .recommendations-list li:last-child {
             border-bottom: none;
         }

         .recommendations-list i {
             width: 24px;
             color: var(--primary);
         }

         .recommendations-list .recommendation-text {
             flex: 1;
             font-size: 14px;
             color: var(--dark);
         }

         .recommendations-list .recommendation-link {
             color: var(--primary);
             text-decoration: none;
             font-size: 13px;
         }

         @media (max-width: 1024px) {
             .factors-grid {
                 grid-template-columns: repeat(2, 1fr);
                 gap: 16px;
             }

             .weight-bars {
                 flex-direction: column;
             }
         }

         @media (max-width: 768px) {
             body {
                 padding: 16px;
             }

             .health-main {
                 padding: 24px;
             }

             .score-ring-container {
                 width: 160px;
                 height: 160px;
             }

             .score-number {
                 font-size: 40px;
             }

             .score-number span {
                 font-size: 18px;
             }

             .factors-grid {
                 grid-template-columns: 1fr;
             }
         }
     </style>
 </head>

 <body>



     <!-- Main Health Score Card -->
     <div class="health-main" id="healthMain">
         <div class="score-ring-container">
             <canvas id="healthScoreCanvas" class="score-ring" width="220" height="220"></canvas>
             <div class="score-number" id="scoreNumber">--<span>/100</span></div>
         </div>
         <div class="score-status" id="scoreStatus">Loading...</div>
         <div class="score-message" id="scoreMessage"></div>
     </div>

     <!-- Five Factors -->
     <div class="factors-section">
         <div class="section-header">
             <h2><i class="fas fa-chart-pie"></i> Five Factors</h2>
         </div>
         <div class="factors-grid" id="factorsGrid">
             <div class="factor-card">
                 <div class="factor-icon cash"><i class="fas fa-coins"></i></div>
                 <div class="factor-name">Cash Ratio</div>
                 <div class="factor-value" id="cashValue">--</div>
                 <div class="factor-points" id="cashPoints">--/25 pts</div>
                 <div class="factor-progress">
                     <div class="factor-progress-fill" id="cashProgress" style="width: 0%; background: var(--primary);"></div>
                 </div>
             </div>
             <div class="factor-card">
                 <div class="factor-icon overdue"><i class="fas fa-exclamation-circle"></i></div>
                 <div class="factor-name">Overdue Invoices</div>
                 <div class="factor-value" id="overdueValue">--</div>
                 <div class="factor-points" id="overduePoints">--/25 pts</div>
                 <div class="factor-progress">
                     <div class="factor-progress-fill" id="overdueProgress" style="width: 0%; background: var(--critical);"></div>
                 </div>
             </div>
             <div class="factor-card">
                 <div class="factor-icon expense"><i class="fas fa-chart-line"></i></div>
                 <div class="factor-name">Expense Growth</div>
                 <div class="factor-value" id="expenseValue">--</div>
                 <div class="factor-points" id="expensePoints">--/20 pts</div>
                 <div class="factor-progress">
                     <div class="factor-progress-fill" id="expenseProgress" style="width: 0%; background: var(--warning);"></div>
                 </div>
             </div>
             <div class="factor-card">
                 <div class="factor-icon runway"><i class="fas fa-calendar-week"></i></div>
                 <div class="factor-name">Months of Runway</div>
                 <div class="factor-value" id="runwayValue">--</div>
                 <div class="factor-points" id="runwayPoints">--/25 pts</div>
                 <div class="factor-progress">
                     <div class="factor-progress-fill" id="runwayProgress" style="width: 0%; background: var(--success);"></div>
                 </div>
             </div>
             <div class="factor-card">
                 <div class="factor-icon compliance"><i class="fas fa-calendar-check"></i></div>
                 <div class="factor-name">Compliance</div>
                 <div class="factor-value" id="complianceValue">--</div>
                 <div class="factor-points" id="compliancePoints">--/20 pts</div>
                 <div class="factor-progress">
                     <div class="factor-progress-fill" id="complianceProgress" style="width: 0%; background: var(--info);"></div>
                 </div>
             </div>
         </div>
     </div>



     <!-- Recommendations -->
     <div class="recommendations-section" id="recommendationsSection">
         <div class="recommendations-header">
             <h3><i class="fas fa-lightbulb"></i> Recommendations</h3>
         </div>
         <ul class="recommendations-list" id="recommendationsList">
             <li><i class="fas fa-spinner fa-pulse"></i> Loading recommendations...</li>
         </ul>
     </div>

     <div id="notificationContainer"></div>

     <script>
         function showNotification(title, message, type = 'info', duration = 4000) {
             const container = document.getElementById('notificationContainer');
             const notification = document.createElement('div');
             notification.className = `notification ${type}`;
             let icon = '';
             switch (type) {
                 case 'success':
                     icon = '<i class="fas fa-check-circle"></i>';
                     break;
                 case 'error':
                     icon = '<i class="fas fa-exclamation-circle"></i>';
                     break;
                 case 'warning':
                     icon = '<i class="fas fa-exclamation-triangle"></i>';
                     break;
                 default:
                     icon = '<i class="fas fa-info-circle"></i>';
             }
             notification.innerHTML = `<div class="notification-content"><div class="notification-icon">${icon}</div><div class="notification-message"><strong>${title}</strong><p>${message}</p></div><div class="notification-close" onclick="this.closest('.notification').remove()"><i class="fas fa-times"></i></div></div>`;
             container.appendChild(notification);
             setTimeout(() => notification.classList.add('show'), 10);
             setTimeout(() => {
                 notification.classList.remove('show');
                 setTimeout(() => notification.remove(), 300);
             }, duration);
         }

         function drawScoreRing(score) {
             const canvas = document.getElementById('healthScoreCanvas');
             const ctx = canvas.getContext('2d');
             const centerX = 110,
                 centerY = 110,
                 radius = 100;
             const startAngle = -0.5 * Math.PI;
             const endAngle = startAngle + (score / 100) * 2 * Math.PI;

             ctx.clearRect(0, 0, 220, 220);

             ctx.beginPath();
             ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
             ctx.strokeStyle = '#e2e8f0';
             ctx.lineWidth = 14;
             ctx.stroke();

             ctx.beginPath();
             ctx.arc(centerX, centerY, radius, startAngle, endAngle);

             let color;
             if (score >= 70) color = '#3b82f6';
             else if (score >= 50) color = '#f59e0b';
             else color = '#dc2626';

             ctx.strokeStyle = color;
             ctx.lineWidth = 14;
             ctx.lineCap = 'round';
             ctx.stroke();

             document.getElementById('scoreNumber').innerHTML = Math.floor(score) + '<span>/100</span>';
         }

         function loadHealthScore() {
             fetch('../../api/api_csv/health_score.php')
                 .then(response => response.json())
                 .then(data => {
                     if (data.success) {
                         const hs = data.health_score;
                         drawScoreRing(hs.score);
                         document.getElementById('scoreStatus').innerHTML = hs.status;
                         document.getElementById('scoreMessage').innerHTML = hs.message;

                         // Update factors
                         const breakdown = hs.breakdown;

                         document.getElementById('cashValue').innerHTML = breakdown.cash_ratio.value;
                         document.getElementById('cashPoints').innerHTML = breakdown.cash_ratio.points + '/25 pts';
                         document.getElementById('cashProgress').style.width = (breakdown.cash_ratio.points / 25 * 100) + '%';

                         document.getElementById('overdueValue').innerHTML = breakdown.overdue_invoices.value;
                         document.getElementById('overduePoints').innerHTML = breakdown.overdue_invoices.points + '/25 pts';
                         document.getElementById('overdueProgress').style.width = (breakdown.overdue_invoices.points / 25 * 100) + '%';

                         document.getElementById('expenseValue').innerHTML = breakdown.expense_growth.value;
                         document.getElementById('expensePoints').innerHTML = breakdown.expense_growth.points + '/20 pts';
                         document.getElementById('expenseProgress').style.width = (breakdown.expense_growth.points / 20 * 100) + '%';

                         document.getElementById('runwayValue').innerHTML = breakdown.runway.value;
                         document.getElementById('runwayPoints').innerHTML = breakdown.runway.points + '/25 pts';
                         document.getElementById('runwayProgress').style.width = (breakdown.runway.points / 25 * 100) + '%';

                         document.getElementById('complianceValue').innerHTML = breakdown.compliance.value;
                         document.getElementById('compliancePoints').innerHTML = breakdown.compliance.points + '/20 pts';
                         document.getElementById('complianceProgress').style.width = (breakdown.compliance.points / 20 * 100) + '%';

                         // Recommendations
                         displayRecommendations(hs.score, breakdown);
                     }
                 })
                 .catch(error => console.error('Error loading health score:', error));
         }

         function displayRecommendations(score, breakdown) {
             const container = document.getElementById('recommendationsList');
             let recommendations = [];

             if (breakdown.cash_ratio.points < 20) {
                 recommendations.push({
                     text: 'Improve your cash ratio by collecting outstanding payments faster.',
                     link: '../details/invoices.php',
                     linkText: 'View Invoices →'
                 });
             }
             if (breakdown.overdue_invoices.points < 20) {
                 recommendations.push({
                     text: 'You have overdue invoices. Send reminders to customers.',
                     link: '../details/invoices.php',
                     linkText: 'Send Reminders →'
                 });
             }
             if (breakdown.expense_growth.points < 15) {
                 recommendations.push({
                     text: 'Your expenses are growing faster than average. Review your spending.',
                     link: '../details/expenses.php',
                     linkText: 'Review Expenses →'
                 });
             }
             if (breakdown.runway.points < 15) {
                 recommendations.push({
                     text: 'Your cash runway is low. Focus on collecting overdue payments.',
                     link: '../details/invoices.php',
                     linkText: 'View Overdue →'
                 });
             }
             if (breakdown.compliance.points < 15 && breakdown.compliance.value !== '0 overdue, 0 upcoming') {
                 recommendations.push({
                     text: 'You have upcoming or overdue compliance deadlines.',
                     link: '../details/compliance.php',
                     linkText: 'View Calendar →'
                 });
             }

             if (recommendations.length === 0) {
                 container.innerHTML = '<li><i class="fas fa-check-circle" style="color: var(--success);"></i> Great job! Your business is in excellent health. Keep it up!</li>';
             } else {
                 let html = '';
                 for (let i = 0; i < recommendations.length; i++) {
                     html += `<li>
                        <i class="fas fa-arrow-right"></i>
                        <span class="recommendation-text">${recommendations[i].text}</span>
                        <a href="javascript:void(0)" onclick="window.location.href='${recommendations[i].link}'" class="recommendation-link">${recommendations[i].linkText}</a>
                    </li>`;
                 }
                 container.innerHTML = html;
             }
         }

         const notificationStyle = document.createElement('style');
         notificationStyle.textContent = `.notification { position: fixed; top: 20px; right: 20px; z-index: 3000; min-width: 320px; max-width: 400px; background: white; border-radius: 5px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); transform: translateX(400px); transition: transform 0.3s ease-in-out; overflow: hidden; } .notification.show { transform: translateX(0); } .notification-content { padding: 16px; display: flex; align-items: flex-start; gap: 12px; } .notification-icon { flex-shrink: 0; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; } .notification-icon i { font-size: 14px; } .notification-message { flex: 1; } .notification-message strong { display: block; margin-bottom: 4px; } .notification-message p { font-size: 13px; color: #666; margin: 0; } .notification-close { cursor: pointer; color: #999; font-size: 12px; } .notification-close:hover { color: #666; } .notification.success { border-left: 4px solid #10b981; } .notification.success .notification-icon { background: #d1fae5; color: #10b981; } .notification.error { border-left: 4px solid #dc2626; } .notification.error .notification-icon { background: #fee2e2; color: #dc2626; } .notification.info { border-left: 4px solid #3b82f6; } .notification.info .notification-icon { background: #dbeafe; color: #3b82f6; } .notification.warning { border-left: 4px solid #f59e0b; } .notification.warning .notification-icon { background: #fed7aa; color: #f59e0b; }`;
         document.head.appendChild(notificationStyle);

         loadHealthScore();
         setInterval(loadHealthScore, 30000);
     </script>

 </body>

 </html>