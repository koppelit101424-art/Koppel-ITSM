<!-- Charts Section -->
 
    <!-- Monthly Trend -->
    <div class="row mb-4">
       <div class="col-md-8">
            <div class="card " >
                <div class="card-header">
                    <h5>Available Stock Per Item </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="stockPerItemChart" height="400"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Monthly Asset Additions (Last 6 Months)</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div> 
    <!-- Status Overview -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Asset Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="250"></canvas>
                </div>
            </div>
        </div>
        <!-- Category Distribution -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Assets by Category</h5>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>