<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Evaluation Form</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background-color:#f4f6f9;
        }

        .card{
            border-radius:15px;
        }

        .company-logo{
            width:300px;
            height:100px;
            object-fit:contain;
        }

        .form-title{
            font-weight:bold;
            color:darkblue;
        }

        .required{
            color:red;
        }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="card shadow p-4">

        <!-- Company Header -->
        <div class="text-center mb-4">
            <img src="koppel.png" alt="Company Logo" class="company-logo">

            <!-- <h2 class="form-title">KOPPEL INC.</h2> -->
            <p class="text-muted">PERFORMANCE APPRAISAL FORM</p>
        </div> <br>

        <!-- Form -->
        <form action="save_evaluation.php" method="POST">

            <div class="row">

                <!-- Employee Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Employee Name <span class="required">*</span>
                    </label>

                    <input 
                        type="text" 
                        name="employee_name" 
                        class="form-control"
                        placeholder="e.g. Juan Dela Cruz"
                        required>
                </div>

                <!-- Immediate Superior -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Immediate Superior
                    </label>

                    <input 
                        type="text" 
                        name="immediate_superior" 
                        class="form-control"
                        placeholder="e.g. Maria Santos">
                </div>

                <!-- Position -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Position <span class="required">*</span>
                    </label>

                    <input 
                        type="text" 
                        name="position" 
                        class="form-control"
                        placeholder="e.g. IT Support Specialist"
                        required>
                </div>

                <!-- Employment Status -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Employment Status
                    </label>

                    <select name="employment_status" class="form-select">
                        <option value="">Select Status</option>
                        <option value="Regular">Regular</option>
                        <option value="Probationary">Probationary</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Temporary">Temporary</option>
                    </select>
                </div>

                <!-- Department -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Department <span class="required">*</span>
                    </label>

                    <input 
                        type="text" 
                        name="department" 
                        class="form-control"
                        placeholder="e.g. Information Technology"
                        required>
                </div>


                <!-- Months / Years in Position -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Months/Years in Position
                    </label>

                    <input 
                        type="text" 
                        name="years_in_position" 
                        class="form-control"
                        placeholder="e.g. 2 Years 5 Months">
                </div>

                <!-- Date Hired -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">
                        Date Hired <span class="required">*</span>
                    </label>

                    <input 
                        type="date" 
                        name="date_hired" 
                        class="form-control"
                        required>
                </div>

                <!-- Evaluation Coverage -->
                <div class="col-md-3 mb-3">
                    <label class="form-label">
                        Evaluation Start Date
                    </label>

                    <input 
                        type="date" 
                        name="eval_start" 
                        class="form-control">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">
                        Evaluation End Date
                    </label>

                    <input 
                        type="date" 
                        name="eval_end" 
                        class="form-control">
                </div>

            </div><br>
            <div class="instruction">
                Instructions to the Rater:	<br><br>														
                                                                            
                1. Study carefully the descriptive statement under each of the different factors defined and write down the score (within the range) which clearly describes the work performance of the employee being rated.															
                (Score must be filled-up in the space provided.) 															
                <i> Remarks portion is alloted for notation to justify the rating done based on your subordinates' KRA & departmental objective	</i><br><br>														
                                                                            
                2. Appraise the employee's performance only if he has stayed in your Section/Department for at least 3 months during the appraisal period; otherwise the appraisal period should be done by the employee's previous superior.	<br><br>														
                                                                            
                3. Rate the employee on each factor independent of the other factors. Do not be influenced by prejudice or pity; disregard irrelevant factors such as age, length of service, consaguinity/affinity, educational attainment, etc.		<br><br>													
                                                                            
                4. Discuss with the employee your appraisal of his/her performance.		<br><br>													
                                                                            
                5. Submit to HR Dept. accomplished form for computation & tabulation.		<br><br>
            </div>
            <div class="rating">
                <h1>I. OUTPUT EVALUATION - 60%</h1><br>
                <h3>Quantity of Work (40%)</h3>
            </div>
                <!-- OUTPUT EVALUATION -->
                <table class="table table-bordered">

                    <!-- TITLE -->
                    <tr>
                        <th colspan="4" class="section-title">
                            I. OUTPUT EVALUATION - 60%
                        </th>
                    </tr>

                    <!-- CATEGORY -->
                    <tr>
                        <td colspan="4" class="category-header">
                            A. QUANTITY OF WORK (40%) <br>
                            1. Quantity-Amount of work completed by employee within specific time.
                        </td>
                    </tr>

                    <!-- QUESTION -->
                    <tr class="table-light">
                        <th width="10%">Rating</th>
                        <th width="20%">Scale</th>
                        <th>Description</th>
                        <th width="20%">Employee Rating</th>
                    </tr>

                    <!-- OUTSTANDING -->
                    <tr>
                        <td>1</td>
                        <td class="rating-label">Outstanding</td>
                        <td>
                            Turns out unusually high volume of work.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quantity_work"
                                   value="1"
                                   required>
                        </td>
                    </tr>

                    <!-- VERY SATISFACTORY -->
                    <tr>
                        <td>1.1 - 2.0</td>
                        <td class="rating-label">
                            Very Satisfactory
                        </td>

                        <td>
                            Completes above average amount of work.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quantity_work"
                                   value="2">
                        </td>
                    </tr>

                    <!-- SATISFACTORY -->
                    <tr>
                        <td>2.1 - 3.0</td>
                        <td class="rating-label">
                            Satisfactory
                        </td>

                        <td>
                            Completes regular work required within reasonable time.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quantity_work"
                                   value="3">
                        </td>
                    </tr>

                    <!-- NEEDS IMPROVEMENT -->
                    <tr>
                        <td>3.1 - 4.0</td>
                        <td class="rating-label">
                            Needs Improvement
                        </td>

                        <td>
                            Does not perform or not able to deliver normal requirement.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quantity_work"
                                   value="4">
                        </td>
                    </tr>

                    <!-- POOR -->
                    <tr>
                        <td>4.1 - 5.0</td>
                        <td class="rating-label text-danger">
                            Poor
                        </td>

                        <td>
                            Poor performance and output.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quantity_work"
                                   value="5">
                        </td>
                    </tr>
                    <tr>
                                        <!-- REMARKS -->
                <div class="mt-4">
                    <label class="form-label fw-bold">
                        Evaluator Remarks
                    </label>

                    <textarea class="form-control"
                              name="remarks"
                              rows="4"
                              placeholder="Enter evaluator comments here..."></textarea>
                </div>
                    </tr>

                    <!-- QUALITY OF WORK -->
                    <tr>
                        <td colspan="4" class="category-header">
                            B. QUALITY OF WORK (60%) <br>
                            1. Quality  - Extent of completeness, neatness and orderliness of the job performed in accordance with set standards.
                        </td>
                    </tr>

                    <!-- QUALITY -->
                    <tr class="table-light">
                        <th>Rating</th>
                        <th>Scale</th>
                        <th>Description</th>
                        <th>Employee Rating</th>
                    </tr>

                    <tr>
                        <td>1</td>
                        <td class="rating-label">Outstanding</td>
                        <td>
                            Work exceptionally complete, neat and orderly.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quality_work"
                                   value="1"
                                   required>
                        </td>
                    </tr>

                    <tr>
                        <td>1.1 - 2.0</td>
                        <td class="rating-label">
                            Very Satisfactory
                        </td>

                        <td>
                            Does very good work and presentable.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quality_work"
                                   value="2">
                        </td>
                    </tr>

                    <tr>
                        <td>2.1 - 3.0</td>
                        <td class="rating-label">
                            Satisfactory
                        </td>

                        <td>
                            Fairly good work and acceptable.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quality_work"
                                   value="3">
                        </td>
                    </tr>

                    <tr>
                        <td>3.1 - 4.0</td>
                        <td class="rating-label">
                            Needs Improvement
                        </td>

                        <td>
                            Standard is not met sometimes.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quality_work"
                                   value="4">
                        </td>
                    </tr>

                    <tr>
                        <td>4.1 - 5.0</td>
                        <td class="rating-label text-danger">
                            Poor
                        </td>

                        <td>
                            Work below company standards.
                        </td>

                        <td>
                            <input type="radio"
                                   name="quality_work"
                                   value="5">
                        </td>
                    </tr>

                </table>
    </div>																									
</div>
</body>
</html>
