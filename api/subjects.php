<?php
// api/subjects.php - Subjects Catalog REST API
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name, category, created_at FROM subjects ORDER BY name ASC");
    $subjects = $stmt->fetchAll();
    echo json_encode(['status' => 'success', 'data' => $subjects]);
} elseif ($method === 'POST') {
    $user = requireRole(['admin']);
    $input = getInput();

    $subId = trim($input['id'] ?? '');
    $name = trim($input['name'] ?? '');
    $category = trim($input['category'] ?? 'STEM');

    if (empty($name)) {
        jsonError(400, 'Subject name required.');
    }

    // Check duplicate name
    $stDup = $pdo->prepare("SELECT COUNT(*) as cnt FROM subjects WHERE LOWER(name) = ? AND id != ?");
    $stDup->execute([strtolower($name), $subId]);
    if ($stDup->fetch()['cnt'] > 0) {
        jsonError(400, "A subject named '$name' already exists in the catalog.");
    }

    if (!empty($subId)) {
        // Verify ID exists
        $stEx = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
        $stEx->execute([$subId]);
        $existing = $stEx->fetch();
        if (!$existing) {
            jsonError(404, 'Subject record not found.');
        }

        $oldName = $existing['name'];

        $stmt = $pdo->prepare("UPDATE subjects SET name = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $category, $subId]);

        // Propagate name change to matches, sessions, and tutors
        if (strtolower($oldName) !== strtolower($name)) {
            $stM = $pdo->prepare("UPDATE matches SET subject = ? WHERE LOWER(subject) = ?");
            $stM->execute([$name, strtolower($oldName)]);

            $stS = $pdo->prepare("UPDATE sessions SET subject = ? WHERE LOWER(subject) = ?");
            $stS->execute([$name, strtolower($oldName)]);

            $stT = $pdo->query("SELECT id, subjects FROM tutors");
            $allTuts = $stT->fetchAll();
            $stUpdTut = $pdo->prepare("UPDATE tutors SET subjects = ? WHERE id = ?");
            foreach ($allTuts as $t) {
                $subs = array_map('trim', explode(',', $t['subjects']));
                $changed = false;
                for ($i = 0; $i < count($subs); $i++) {
                    if (strtolower($subs[$i]) === strtolower($oldName)) {
                        $subs[$i] = $name;
                        $changed = true;
                    }
                }
                if ($changed) {
                    $stUpdTut->execute([implode(', ', $subs), $t['id']]);
                }
            }
        }

        echo json_encode(['status' => 'success', 'message' => 'Subject updated successfully.']);
    } else {
        // Add subject
        $newSubId = newId('SUB-');
        $stmt = $pdo->prepare("INSERT INTO subjects (id, name, category) VALUES (?, ?, ?)");
        $stmt->execute([$newSubId, $name, $category]);
        echo json_encode([
            'status' => 'success',
            'message' => 'Subject created.',
            'data' => ['id' => $newSubId, 'name' => $name, 'category' => $category]
        ]);
    }
} elseif ($method === 'DELETE') {
    $user = requireRole(['admin']);
    $input = getInput();
    $subId = trim($input['id'] ?? ($_GET['id'] ?? ''));

    if (empty($subId)) {
        jsonError(400, 'Subject ID required.');
    }

    $stmtFetch = $pdo->prepare("SELECT name FROM subjects WHERE id = ?");
    $stmtFetch->execute([$subId]);
    $subRow = $stmtFetch->fetch();

    if (!$subRow) {
        jsonError(404, 'Subject not found.');
    }

    $subName = $subRow['name'];

    // Exact matching in tutors subjects list
    $stT = $pdo->query("SELECT subjects FROM tutors");
    $tutorRows = $stT->fetchAll();
    $usedInTutors = false;
    foreach ($tutorRows as $tr) {
        $subs = array_map('strtolower', array_map('trim', explode(',', $tr['subjects'])));
        if (in_array(strtolower($subName), $subs, true)) {
            $usedInTutors = true;
            break;
        }
    }
    if ($usedInTutors) {
        jsonError(400, "Cannot delete subject '$subName' because it is offered by existing tutors.");
    }

    // Check usage in matches
    $stM = $pdo->prepare("SELECT COUNT(*) as cnt FROM matches WHERE LOWER(subject) = ?");
    $stM->execute([strtolower($subName)]);
    if ($stM->fetch()['cnt'] > 0) {
        jsonError(400, "Cannot delete subject '$subName' because it is referenced in match requests.");
    }

    // Check usage in sessions
    $stS = $pdo->prepare("SELECT COUNT(*) as cnt FROM sessions WHERE LOWER(subject) = ?");
    $stS->execute([strtolower($subName)]);
    if ($stS->fetch()['cnt'] > 0) {
        jsonError(400, "Cannot delete subject '$subName' because it is referenced in tutoring sessions.");
    }

    $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $stmt->execute([$subId]);

    echo json_encode(['status' => 'success', 'message' => 'Subject deleted.']);
} else {
    jsonError(405, 'Method not allowed');
}
