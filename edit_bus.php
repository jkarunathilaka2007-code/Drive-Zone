<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'owner') {
    header("Location: login.php");
    exit();
}

$bus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$owner_id = $_SESSION['user_id'];

// Fetch bus details
$sql = "SELECT * FROM buses WHERE id = $bus_id AND owner_id = $owner_id";
$result = $conn->query($sql);
$bus = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $bus_no = mysqli_real_escape_string($conn, $_POST['bus_number']);
    $rows = intval($_POST['total_rows']);
    $left_side = intval($_POST['seats_per_column']); // වම් පැත්ත
    $right_side = intval($_POST['seats_per_row']);  // දකුණු පැත්ත
    $last_row = intval($_POST['last_row_seats']);
    $total_seats = intval($_POST['number_of_seats']);

    // Update Query
    $update_sql = "UPDATE buses SET brand=?, bus_number=?, total_rows=?, seats_per_column=?, seats_per_row=?, last_row_seats=?, number_of_seats=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssiiiiii", $brand, $bus_no, $rows, $left_side, $right_side, $last_row, $total_seats, $bus_id);

    if ($stmt->execute()) {
        echo "<script>alert('Layout Updated Successfully!'); window.location.href='my_fleet.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Master Layout Editor | DriveZone</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap');
        body { font-family: 'Outfit', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen p-4 md:p-10">

    <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10">
        
        <div class="lg:col-span-5 bg-white p-8 rounded-[40px] shadow-2xl border border-gray-100 h-fit">
            <h2 class="text-3xl font-black text-gray-800 mb-8 italic">Configure <span class="text-blue-600">Seats</span></h2>

            <form action="" method="POST" class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="brand" value="<?php echo $bus['brand']; ?>" placeholder="Brand" class="p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-blue-600 outline-none font-bold">
                    <input type="text" name="bus_number" value="<?php echo $bus['bus_number']; ?>" placeholder="Bus No" class="p-4 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-blue-600 outline-none font-bold">
                </div>

                <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100 space-y-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Main Rows (මැද පේළි ගණන)</label>
                        <input type="number" id="rows_input" name="total_rows" value="<?php echo $bus['total_rows']; ?>" oninput="updateLayout()" class="w-full p-4 rounded-2xl border-2 border-white focus:border-blue-600 outline-none font-bold shadow-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Left Side (වම)</label>
                            <input type="number" id="left_input" name="seats_per_column" value="<?php echo $bus['seats_per_column']; ?>" oninput="updateLayout()" class="w-full p-4 rounded-2xl border-2 border-white focus:border-blue-600 outline-none font-bold shadow-sm bg-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Right Side (දකුණ)</label>
                            <input type="number" id="right_input" name="seats_per_row" value="<?php echo isset($bus['seats_per_row']) ? $bus['seats_per_row'] : 2; ?>" oninput="updateLayout()" class="w-full p-4 rounded-2xl border-2 border-white focus:border-blue-600 outline-none font-bold shadow-sm bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Last Row Seats (පිටුපස පේළිය)</label>
                        <input type="number" id="last_row_input" name="last_row_seats" value="<?php echo isset($bus['last_row_seats']) ? $bus['last_row_seats'] : 5; ?>" oninput="updateLayout()" class="w-full p-4 rounded-2xl border-2 border-white focus:border-blue-600 outline-none font-bold shadow-sm">
                    </div>

                    <div class="pt-4 border-t border-slate-200">
                        <label class="block text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-1">Total Capacity</label>
                        <input type="text" id="total_seats_input" name="number_of_seats" value="<?php echo $bus['number_of_seats']; ?>" readonly class="text-4xl font-black text-blue-600 bg-transparent outline-none">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-black py-5 rounded-[25px] shadow-xl hover:bg-blue-700 transition">
                    Apply & Save Layout
                </button>
            </form>
        </div>

        <div class="lg:col-span-7 bg-slate-900 p-8 rounded-[50px] shadow-2xl flex flex-col items-center">
            <div class="w-64 h-2 bg-slate-800 rounded-full mb-10 opacity-50"></div>
            
            <div class="w-full max-w-md">
                <div class="flex justify-between px-6 mb-12">
                    <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center border-2 border-orange-400 text-white shadow-lg"><i class="fas fa-steering-wheel"></i></div>
                    <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center border-2 border-emerald-400 text-white shadow-lg"><i class="fas fa-id-badge"></i></div>
                </div>

                <div class="max-h-[600px] overflow-y-auto pr-2 scrollbar-hide">
                    <div id="seat_container" class="space-y-3"></div>
                    <div id="last_row_view" class="mt-3 pt-6 border-t border-slate-800 flex justify-center space-x-1.5"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateLayout() {
            const rows = parseInt(document.getElementById('rows_input').value) || 0;
            const leftSide = parseInt(document.getElementById('left_input').value) || 0;
            const rightSide = parseInt(document.getElementById('right_input').value) || 0;
            const lastRowVal = parseInt(document.getElementById('last_row_input').value) || 0;
            
            const container = document.getElementById('seat_container');
            const lastRowContainer = document.getElementById('last_row_view');
            
            container.innerHTML = '';
            lastRowContainer.innerHTML = '';
            let seatCounter = 1;

            // Generate Main Rows
            for (let i = 0; i < rows; i++) {
                const rowDiv = document.createElement('div');
                rowDiv.className = "flex items-center justify-center space-x-4";
                
                // Left Part
                const lPart = document.createElement('div');
                lPart.className = "flex space-x-1.5";
                for (let j = 0; j < leftSide; j++) lPart.innerHTML += createSeat(seatCounter++);
                
                // Aisle (මැද මාවත)
                const aisle = `<div class="w-10 flex justify-center opacity-20 text-white text-[10px] font-bold">AISLE</div>`;
                
                // Right Part
                const rPart = document.createElement('div');
                rPart.className = "flex space-x-1.5";
                for (let j = 0; j < rightSide; j++) rPart.innerHTML += createSeat(seatCounter++);

                rowDiv.appendChild(lPart);
                rowDiv.innerHTML += aisle;
                rowDiv.appendChild(rPart);
                container.appendChild(rowDiv);
            }

            // Generate Last Row
            for (let k = 0; k < lastRowVal; k++) {
                lastRowContainer.innerHTML += createSeat(seatCounter++);
            }

            document.getElementById('total_seats_input').value = seatCounter - 1;
        }

        function createSeat(num) {
            return `<div class="w-10 h-10 bg-blue-600/10 border border-blue-500/30 rounded-xl flex items-center justify-center text-[11px] font-black text-blue-400 shadow-sm transition-all hover:bg-blue-600 hover:text-white cursor-default">${num}</div>`;
        }

        window.onload = updateLayout;
    </script>
</body>
</html>