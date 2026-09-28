import 'package:flutter/material.dart';
import 'manage_students_screen.dart';
import 'manage_tutor_matching_screen.dart';
import 'manage_schedule_screen.dart';
import 'manage_payments_screen.dart';
import 'manage_notifications_screen.dart';
import 'manage_reports_screen.dart';

class AdminDashboardScreen extends StatelessWidget {
  const AdminDashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0C2B3E), // Dark theme matching reference UI
      appBar: AppBar(
        title: const Text('Admin Portal Dashboard'),
        backgroundColor: const Color(0xFF081C29),
        foregroundColor: Colors.white,
        elevation: 2,
      ),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Management Overview',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Select a module below to manage operations',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 20),
              Expanded(
                child: SingleChildScrollView(
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Column 1: Manage Students
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Students',
                          subItems: [
                            ModuleSubItem('View Student List', (ctx) => const ManageStudentsScreen(initialTab: 0)),
                            ModuleSubItem('Validate Student List', (ctx) => const ManageStudentsScreen(initialTab: 1)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Column 2: Manage Tutor Matching
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Tutor Matching',
                          subItems: [
                            ModuleSubItem('Review Matching Results', (ctx) => const ManageTutorMatchingScreen(initialTab: 0)),
                            ModuleSubItem('Approve Matching Sessions', (ctx) => const ManageTutorMatchingScreen(initialTab: 1)),
                            ModuleSubItem('Cancel Matching Sessions', (ctx) => const ManageTutorMatchingScreen(initialTab: 2)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Column 3: Manage Schedule
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Schedule',
                          subItems: [
                            ModuleSubItem('New Calendar Schedule', (ctx) => const ManageScheduleScreen(initialTab: 0)),
                            ModuleSubItem('Modify Sessions Schedule', (ctx) => const ManageScheduleScreen(initialTab: 1)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Column 4: Manage Payments
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Payments',
                          subItems: [
                            ModuleSubItem('New Payment Records', (ctx) => const ManagePaymentsScreen(initialTab: 0)),
                            ModuleSubItem('Confirm Payment Status', (ctx) => const ManagePaymentsScreen(initialTab: 1)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Column 5: Manage Notifications
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Notifications',
                          subItems: [
                            ModuleSubItem('Send Notifications', (ctx) => const ManageNotificationsScreen(initialTab: 0)),
                            ModuleSubItem('View Notifications', (ctx) => const ManageNotificationsScreen(initialTab: 1)),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Column 6: Manage Reports
                      Expanded(
                        child: _buildMainModuleColumn(
                          context,
                          title: 'Manage Reports',
                          subItems: [
                            ModuleSubItem('View Completed Tutoring', (ctx) => const ManageReportsScreen(initialTab: 0)),
                            ModuleSubItem('View Weekly Sessions', (ctx) => const ManageReportsScreen(initialTab: 1)),
                            ModuleSubItem('View Monthly Sessions', (ctx) => const ManageReportsScreen(initialTab: 2)),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMainModuleColumn(
    BuildContext context, {
    required String title,
    required List<ModuleSubItem> subItems,
  }) {
    return Column(
      children: [
        // Main Header Node
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
          decoration: BoxDecoration(
            color: const Color(0xFF0A4F70),
            border: Border.all(color: Colors.white, width: 2),
            borderRadius: BorderRadius.circular(4),
            boxShadow: const [
              BoxShadow(
                color: Colors.black26,
                blurRadius: 4,
                offset: Offset(0, 2),
              ),
            ],
          ),
          child: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 13,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
        ),
        const SizedBox(height: 12),

        // Connecting vertical branch line
        Container(width: 2, height: 12, color: Colors.cyanAccent),
        const SizedBox(height: 4),

        // Sub Items List
        ...subItems.map(
          (sub) => Container(
            margin: const EdgeInsets.only(bottom: 12),
            child: InkWell(
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) => sub.builder(context)),
                );
              },
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F3E56),
                  border: Border.all(color: Colors.white70, width: 1.5),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  sub.label,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class ModuleSubItem {
  final String label;
  final WidgetBuilder builder;

  ModuleSubItem(this.label, this.builder);
}
