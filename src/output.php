<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WordPress Child Plugin Tool</title>
</head>
<body>
    <h1>Modify Plugin Tool</h1>
    
    <!-- Display operation type and plugin paths -->
    <div><strong>Operation:</strong> <?php echo htmlspecialchars($output->operation); ?></div>
    <div><strong>Parent plugin path:</strong> <?php echo htmlspecialchars(remove_server_path($parentPluginDir)); ?></div>
    <div><strong>Child plugin path:</strong> <?php echo htmlspecialchars(remove_server_path($childPluginDir)); ?></div>

    <br>

    <!-- Table for Modified Files -->
    <h2>Modified Files</h2>
    <table>
        <thead>
            <tr>
                <th>File</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($output->modfiedItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item->get_shortpath()); ?></td>
                    <td><?php echo htmlspecialchars($item->msg); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <br>

    <!-- Table for Original Files -->
    <h2>Original Files</h2>
    <table>
        <thead>
            <tr>
                <th>File</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($output->originalItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item->get_shortpath()); ?></td>
                    <td><?php echo htmlspecialchars($item->msg); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
