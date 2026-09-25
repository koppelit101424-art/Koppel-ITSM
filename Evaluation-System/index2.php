<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Performance Appraisal Form</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Professional Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>

        body{
            background:#f1f4f9;
            font-family:'Inter', sans-serif;
            color:#2d3748;
        }

        .main-card{
            border:none;
            border-radius:18px;
            overflow:hidden;
        }

        .header-section{
            background:white;
            border-bottom:4px solid #0d6efd;
        }

        .company-logo{
            width:260px;
            height:90px;
            object-fit:contain;
        }

        .main-title{
            font-weight:700;
            color:#1a202c;
            letter-spacing:.5px;
        }

        .sub-title{
            color:#718096;
            font-size:14px;
        }

        .section-header{
            background:#0d6efd;
            color:white;
            font-size:17px;
            font-weight:700;
            padding:14px;
        }

        .category-header{
            background:#fff8d6;
            font-weight:700;
            color:#2d3748;
        }

        .table th{
            background:#edf2f7;
            font-size:14px;
        }

        .table td{
            font-size:14px;
            vertical-align:middle;
        }

        .rating-badge{
            font-weight:600;
        }

        .instruction-box{
            background:white;
            padding:25px;
            border-radius:12px;
            border-left:5px solid #0d6efd;
            line-height:1.8;
            font-size:14px;
        }

        .form-label{
            font-weight:600;
            font-size:14px;
        }

        .form-control,
        .form-select{
            border-radius:10px;
            padding:10px;
            font-size:14px;
        }

        textarea{
            resize:none;
        }

        .comment-box{
            min-width:220px;
        }

        .score-display{
            font-weight:700;
            color:#0d6efd;
        }

        .btn-submit{
            padding:12px 40px;
            border-radius:12px;
            font-weight:600;
            font-size:15px;
        }

        .required{
            color:red;
        }

    </style>
</head>
<body>

<div class="container mt-5 mb-5">

    <div class="card shadow-lg main-card">

        <!-- HEADER -->
        <div class="header-section text-center p-4">

            <img src="koppel.png"
                 class="company-logo mb-2">

            <h2 class="main-title">
                PERFORMANCE APPRAISAL FORM
            </h2>

            <div class="sub-title">
                Employee Performance Evaluation System
            </div>

        </div>

        <form action="save_evaluation.php" method="POST">

            <div class="p-4">

                <!-- EMPLOYEE INFO -->
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

                </div>

                <!-- INSTRUCTIONS -->
                <div class="instruction-box mb-4 mt-3">

                    <strong>Instructions to the Rater:</strong><br><br>

                                                                            
                1. Study carefully the descriptive statement under each of the different factors defined and write down the score (within the range) which clearly describes the work performance of the employee being rated.															
                (Score must be filled-up in the space provided.) 															
                <i> Remarks portion is alloted for notation to justify the rating done based on your subordinates' KRA & departmental objective	</i><br><br>														
                                                                            
                2. Appraise the employee's performance only if he has stayed in your Section/Department for at least 3 months during the appraisal period; otherwise the appraisal period should be done by the employee's previous superior.	<br><br>														
                                                                            
                3. Rate the employee on each factor independent of the other factors. Do not be influenced by prejudice or pity; disregard irrelevant factors such as age, length of service, consaguinity/affinity, educational attainment, etc.		<br><br>													
                                                                            
                4. Discuss with the employee your appraisal of his/her performance.		<br><br>													
                                                                            
                5. Submit to HR Dept. accomplished form for computation & tabulation.		<br><br>

                </div>

                <!-- OUTPUT EVALUATION -->
                <div class="section-header">
                    I. OUTPUT EVALUATION - 60%
                </div>

                <table class="table table-bordered">

                    <!-- QUANTITY -->
                    <tr>
                        <td colspan="5" class="category-header">
                            A. QUANTITY OF WORK (40%)<br>
                            <small>
                                Amount of work completed within specific time.
                            </small>
                        </td>
                    </tr>

                    <tr>
                        <th width="10%">Range</th>
                        <th width="18%">Scale</th>
                        <th>Description</th>
                        <th width="12%">Select</th>
                        <th width="12%">Score</th>
                    </tr>

                    <tr>
                        <td>1.0</td>
                        <td class="rating-badge">Outstanding</td>
                        <td>Turns out unusually high volume of work.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quantity_work"
                                   value="1.0"
                                   onclick="setScore('quantity_score','1.0')"
                                   required>
                        </td>

                        <td class="score-display" id="quantity_score">
                            -
                        </td>
                    </tr>

                    <tr>
                        <td>1.1 - 2.0</td>
                        <td class="rating-badge">Very Satisfactory</td>
                        <td>Completes above average amount of work.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quantity_work"
                                   value="1.5"
                                   onclick="setScore('quantity_score','1.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>2.1 - 3.0</td>
                        <td class="rating-badge">Satisfactory</td>
                        <td>Completes regular work within reasonable time.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quantity_work"
                                   value="2.5"
                                   onclick="setScore('quantity_score','2.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>3.1 - 4.0</td>
                        <td class="rating-badge">Needs Improvement</td>
                        <td>Performance sometimes below expectations.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quantity_work"
                                   value="3.5"
                                   onclick="setScore('quantity_score','3.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>4.1 - 5.0</td>
                        <td class="rating-badge text-danger">Poor</td>
                        <td>Unable to meet normal requirements.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quantity_work"
                                   value="4.5"
                                   onclick="setScore('quantity_score','4.5')">
                        </td>

                        <td></td>
                    </tr>

                    <!-- COMMENT -->
                    <tr>
                        <td colspan="5">

                            <label class="form-label">
                                Comment for Quantity of Work
                            </label>

                            <textarea class="form-control comment-box"
                                      name="quantity_comment"
                                      rows="3"
                                      placeholder="Enter comments or justification here..."
                                      required></textarea>

                        </td>
                    </tr>

                    <!-- QUALITY -->
                    <tr>
                        <td colspan="5" class="category-header">
                            B. QUALITY OF WORK (60%)<br>
                            <small>
                                Extent of completeness, neatness and orderliness of the job performed in accordance with set standards.
                            </small>
                        </td>
                    </tr>

                    <tr>
                        <th>Range</th>
                        <th>Scale</th>
                        <th>Description</th>
                        <th>Select</th>
                        <th>Score</th>
                    </tr>

                    <tr>
                        <td>1.0</td>
                        <td class="rating-badge">Outstanding</td>
                        <td>Work exceptionally complete and orderly.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quality_work"
                                   value="1.0"
                                   onclick="setScore('quality_score','1.0')"
                                   required>
                        </td>

                        <td class="score-display" id="quality_score">
                            -
                        </td>
                    </tr>

                    <tr>
                        <td>1.1 - 2.0</td>
                        <td class="rating-badge">Very Satisfactory</td>
                        <td>Very good work and presentable.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quality_work"
                                   value="1.5"
                                   onclick="setScore('quality_score','1.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>2.1 - 3.0</td>
                        <td class="rating-badge">Satisfactory</td>
                        <td>Fairly good and acceptable quality.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quality_work"
                                   value="2.5"
                                   onclick="setScore('quality_score','2.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>3.1 - 4.0</td>
                        <td class="rating-badge">Needs Improvement</td>
                        <td>Standards are not consistently met.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quality_work"
                                   value="3.5"
                                   onclick="setScore('quality_score','3.5')">
                        </td>

                        <td></td>
                    </tr>

                    <tr>
                        <td>4.1 - 5.0</td>
                        <td class="rating-badge text-danger">Poor</td>
                        <td>Below company standards.</td>

                        <td class="text-center">
                            <input type="radio"
                                   name="quality_work"
                                   value="4.5"
                                   onclick="setScore('quality_score','4.5')">
                        </td>

                        <td></td>
                    </tr>

                    <!-- COMMENT -->
                    <tr>
                        <td colspan="5">

                            <label class="form-label">
                                Comment for Quality of Work
                            </label>

                            <textarea class="form-control comment-box"
                                      name="quality_comment"
                                      rows="3"
                                      placeholder="Enter comments or justification here..."
                                      required></textarea>

                        </td>
                    </tr>

                </table>

                <!-- TOTAL -->
                <div class="row mt-4">

                    <div class="col-md-4">
                        <label class="form-label">
                            Quantity Score
                        </label>

                        <input type="text"
                               id="final_quantity"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Quality Score
                        </label>

                        <input type="text"
                               id="final_quality"
                               class="form-control"
                               readonly>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">
                            Overall Rating
                        </label>

                        <input type="text"
                               id="overall_rating"
                               class="form-control fw-bold"
                               readonly>
                    </div>

                </div>

                <!-- SUBMIT -->
                <div class="text-end mt-4">

                    <button type="submit"
                            class="btn btn-primary btn-submit">
                        Submit Evaluation
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<script>

function setScore(elementId, value){

    document.getElementById(elementId).innerHTML = value;

    if(elementId == 'quantity_score'){
        document.getElementById('final_quantity').value = value;
    }

    if(elementId == 'quality_score'){
        document.getElementById('final_quality').value = value;
    }

    computeOverall();

}

function computeOverall(){

    let quantity = parseFloat(document.getElementById('final_quantity').value) || 0;

    let quality = parseFloat(document.getElementById('final_quality').value) || 0;

    if(quantity > 0 && quality > 0){

        let total = ((quantity + quality) / 2).toFixed(2);

        document.getElementById('overall_rating').value = total;

    }

}

</script>

</body>
</html>

*add the 2 and 3 here
* on the score column make it a 1 column and should display the range where the employee will choose 
* do not change anything just add what i instructed
* send the whole code