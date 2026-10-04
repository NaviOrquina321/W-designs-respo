import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManageStudentsScreen extends StatelessWidget {
  final int initialTab;

  const ManageStudentsScreen({super.key, this.initialTab = 0});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      initialIndex: initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Students'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'View Student List'),
              Tab(text: 'Validate Student List'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildViewStudentList(context, store),
                _buildValidateStudentList(context, store),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildViewStudentList(BuildContext context, AdminStore store) {
    final students = store.students;

    if (students.isEmpty) {
      return const Center(child: Text('No students found.'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: students.length,
      itemBuilder: (context, index) {
        final student = students[index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: CircleAvatar(
              backgroundColor: Colors.blueAccent.withAlpha(30),
              child: const Icon(Icons.person, color: Colors.blueAccent),
            ),
            title: Text(
              student.name,
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
            subtitle: Text('${student.email} • ${student.gradeLevel}'),
            trailing: Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: student.isValidated
                    ? Colors.green.withAlpha(30)
                    : Colors.orange.withAlpha(30),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                student.isValidated ? 'Validated' : 'Pending',
                style: TextStyle(
                  color: student.isValidated ? Colors.green : Colors.orange,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildValidateStudentList(BuildContext context, AdminStore store) {
    final unvalidated = store.students.where((s) => !s.isValidated).toList();

    if (unvalidated.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.check_circle_outline, size: 60, color: Colors.green),
            SizedBox(height: 12),
            Text(
              'All students are validated!',
              style: TextStyle(fontSize: 16, color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: unvalidated.length,
      itemBuilder: (context, index) {
        final student = unvalidated[index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                CircleAvatar(
                  backgroundColor: Colors.orange.withAlpha(30),
                  child: const Icon(Icons.person_outline, color: Colors.orange),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        student.name,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                        ),
                      ),
                      Text(
                        '${student.email} • ${student.gradeLevel}',
                        style: const TextStyle(color: Colors.grey, fontSize: 13),
                      ),
                    ],
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                  ),
                  onPressed: () {
                    store.validateStudent(student.id);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text('${student.name} validated!')),
                    );
                  },
                  child: const Text('Validate'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
