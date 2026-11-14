<?php
// Get all levels for sidebar if not already set
if (!isset($levels) && isset($db)) {
    $levelsQuery = "SELECT * FROM levels WHERE parent_id IS NULL ORDER BY sort_order";
    $levelsStmt = $db->prepare($levelsQuery);
    $levelsStmt->execute();
    $levels = $levelsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($levels as &$level) {
        $subQuery = "SELECT * FROM levels WHERE parent_id = :parent_id ORDER BY name";
        $subStmt = $db->prepare($subQuery);
        $subStmt->bindParam(':parent_id', $level['id']);
        $subStmt->execute();
        $level['children'] = $subStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="bg-white rounded-xl shadow-md overflow-hidden sticky top-24">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-5 py-4">
        <h2 class="font-bold text-lg flex items-center gap-2">
            <i class="fas fa-layer-group"></i> Levels
        </h2>
    </div>
    
    <div class="divide-y divide-gray-100">
        <?php if (isset($levels) && !empty($levels)): ?>
            <?php foreach ($levels as $level): ?>
            <div>
                <button class="level-trigger w-full flex items-center justify-between p-4 text-gray-700 font-semibold hover:bg-indigo-50 transition text-left" 
                        data-target="level-<?php echo $level['id']; ?>">
                    <span><?php echo htmlspecialchars($level['name']); ?></span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                </button>
                <div id="level-<?php echo $level['id']; ?>" class="level-content hidden">
                    <div class="bg-gray-50 p-3 space-y-1">
                        <?php if (!empty($level['children'])): ?>
                            <?php foreach ($level['children'] as $child): ?>
                            <a href="/ezeria-library/level.php?slug=<?php echo $child['slug']; ?>" 
                               class="block px-3 py-2 text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                <i class="fas fa-angle-right mr-2 text-indigo-400 text-xs"></i>
                                <?php echo htmlspecialchars($child['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-xs text-gray-400 px-3 py-2">No sub-levels available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-4 text-center text-gray-500">No levels available</div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Stats Sidebar Widget -->
<div class="bg-white rounded-xl shadow-md overflow-hidden mt-6 sticky top-96">
    <div class="bg-gradient-to-r from-green-600 to-teal-600 text-white px-5 py-4">
        <h2 class="font-bold text-lg flex items-center gap-2">
            <i class="fas fa-chart-simple"></i> Quick Stats
        </h2>
    </div>
    <div class="p-4 space-y-3">
        <div class="flex justify-between items-center">
            <span class="text-gray-600 text-sm"><i class="fas fa-file-alt mr-2 text-indigo-500"></i> Total Documents</span>
            <span class="font-bold text-gray-800">12,345+</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-600 text-sm"><i class="fas fa-users mr-2 text-indigo-500"></i> Active Users</span>
            <span class="font-bold text-gray-800">5,678+</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-600 text-sm"><i class="fas fa-download mr-2 text-indigo-500"></i> Total Downloads</span>
            <span class="font-bold text-gray-800">123,456+</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-gray-600 text-sm"><i class="fas fa-book mr-2 text-indigo-500"></i> Books Available</span>
            <span class="font-bold text-gray-800">1,234+</span>
        </div>
    </div>
</div>

<!-- Need Help Widget -->
<div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl shadow-md overflow-hidden mt-6 p-5 text-center sticky top-[600px]">
    <i class="fas fa-headset text-3xl text-indigo-600 mb-3"></i>
    <h3 class="font-bold text-gray-800 mb-2">Need Help?</h3>
    <p class="text-xs text-gray-600 mb-3">Our librarians are here to assist you</p>
    <a href="/ezeria-library/ask-librarian.php" class="inline-block btn-primary text-white text-sm px-4 py-2 rounded-lg transition">
        <i class="fas fa-envelope mr-1"></i> Ask a Librarian
    </a>
</div>

<script>
document.querySelectorAll('.level-trigger').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.dataset.target;
        const content = document.getElementById(targetId);
        const icon = button.querySelector('i');
        
        if (content && icon) {
            document.querySelectorAll('.level-content').forEach(item => {
                if (item.id !== targetId && !item.classList.contains('hidden')) {
                    item.classList.add('hidden');
                    const otherIcon = document.querySelector(`button[data-target="${item.id}"] i`);
                    if (otherIcon) otherIcon.classList.remove('rotate-180');
                }
            });
            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    });
});
</script>