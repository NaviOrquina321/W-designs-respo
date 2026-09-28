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
      backgroundColor: const Color(0xFF071B26), // Dark background matching image.png
      appBar: AppBar(
        title: const Text(
          'ADMIN MANAGEMENT PORTAL',
          style: TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.bold,
            letterSpacing: 1.2,
          ),
        ),
        backgroundColor: const Color(0xFF05131C),
        foregroundColor: Colors.white,
        elevation: 4,
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Top Main Connector Horizontal Line Representation
              Container(
                margin: const EdgeInsets.only(bottom: 20),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFF0E2A3A),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFF1B4F6C)),
                ),
                child: Row(
                  children: const [
                    Icon(Icons.account_tree_outlined, color: Colors.cyanAccent),
                    SizedBox(width: 10),
                    Text(
                      'Admin Function Architecture & Navigation Tree',
                      style: TextStyle(
                        color: Colors.cyanAccent,
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),

              // Interactive Horizontal Tree Layout (Scrollable for desktop/mobile)
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: IntrinsicHeight(
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Module 1: Manage Students
                      _buildTreeColumn(
                        context,
                        title: 'Manage\nStudents',
                        subNodes: [
                          _SubNode('View Student\nList', (ctx) => const ManageStudentsScreen(initialTab: 0)),
                          _SubNode('Validate Student\nList', (ctx) => const ManageStudentsScreen(initialTab: 1)),
                        ],
                      ),
                      const SizedBox(width: 16),

                      // Module 2: Manage Tutor Matching
                      _buildTreeColumn(
                        context,
                        title: 'Manage Tutor\nMatching',
                        subNodes: [
                          _SubNode('Review\nMatching\nResults', (ctx) => const ManageTutorMatchingScreen(initialTab: 0)),
                          _SubNode('Approve\nMatching\nSessions', (ctx) => const ManageTutorMatchingScreen(initialTab: 1)),
                          _SubNode('Cancel\nMatching\nSessions', (ctx) => const ManageTutorMatchingScreen(initialTab: 2)),
                        ],
                      ),
                      const SizedBox(width: 16),

                      // Module 3: Manage Schedule
                      _buildTreeColumn(
                        context,
                        title: 'Manage\nSchedule',
                        subNodes: [
                          _SubNode('New Calendar\nSchedule', (ctx) => const ManageScheduleScreen(initialTab: 0)),
                          _SubNode('Modify Sessions\nSchedule', (ctx) => const ManageScheduleScreen(initialTab: 1)),
                        ],
                      ),
                      const SizedBox(width: 16),

                      // Module 4: Manage Payments
                      _buildTreeColumn(
                        context,
                        title: 'Manage\nPayments',
                        subNodes: [
                          _SubNode('New Payment\nRecords', (ctx) => const ManagePaymentsScreen(initialTab: 0)),
                          _SubNode('Confirm\nPayment Status', (ctx) => const ManagePaymentsScreen(initialTab: 1)),
                        ],
                      ),
                      const SizedBox(width: 16),

                      // Module 5: Manage Notifications
                      _buildTreeColumn(
                        context,
                        title: 'Manage\nNotifications',
                        subNodes: [
                          _SubNode('Send\nNotifications', (ctx) => const ManageNotificationsScreen(initialTab: 0)),
                          _SubNode('View\nNotifications', (ctx) => const ManageNotificationsScreen(initialTab: 1)),
                        ],
                      ),
                      const SizedBox(width: 16),

                      // Module 6: Manage Reports
                      _buildTreeColumn(
                        context,
                        title: 'Manage\nReports',
                        subNodes: [
                          _SubNode('View Completed\nTutoring', (ctx) => const ManageReportsScreen(initialTab: 0)),
                          _SubNode('View Weekly\nSessions', (ctx) => const ManageReportsScreen(initialTab: 1)),
                          _SubNode('View Monthly\nSessions', (ctx) => const ManageReportsScreen(initialTab: 2)),
                        ],
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

  Widget _buildTreeColumn(
    BuildContext context, {
    required String title,
    required List<_SubNode> subNodes,
  }) {
    const nodeWidth = 140.0;

    return SizedBox(
      width: nodeWidth,
      child: Column(
        children: [
          // Top Header Node Card (Exact style matching image.png)
          Container(
            width: nodeWidth,
            height: 60,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: const Color(0xFF0F4866),
              border: Border.all(color: Colors.white, width: 2),
              borderRadius: BorderRadius.circular(2),
              boxShadow: [
                BoxShadow(
                  color: Colors.cyanAccent.withAlpha(40),
                  blurRadius: 6,
                  spreadRadius: 1,
                ),
              ],
            ),
            child: Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 13,
                fontWeight: FontWeight.bold,
                height: 1.2,
              ),
            ),
          ),

          // Main vertical blue connector line
          Container(
            width: 3,
            height: 24,
            color: const Color(0xFF00A2D3),
          ),

          // Sub Nodes Column linked with vertical lines
          ...subNodes.asMap().entries.map((entry) {
            final idx = entry.key;
            final sub = entry.value;

            return Column(
              children: [
                if (idx > 0)
                  Container(
                    width: 3,
                    height: 16,
                    color: const Color(0xFF00A2D3),
                  ),
                InkWell(
                  onTap: () {
                    Navigator.push(
                      context,
                      MaterialPageRoute(builder: (context) => sub.builder(context)),
                    );
                  },
                  borderRadius: BorderRadius.circular(2),
                  child: Container(
                    width: nodeWidth,
                    padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 12),
                    alignment: Alignment.center,
                    constraints: const BoxConstraints(minHeight: 56),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0E3850),
                      border: Border.all(color: Colors.white, width: 1.8),
                      borderRadius: BorderRadius.circular(2),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withAlpha(100),
                          blurRadius: 4,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      sub.title,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        height: 1.25,
                      ),
                    ),
                  ),
                ),
              ],
            );
          }),
        ],
      ),
    );
  }
}

class _SubNode {
  final String title;
  final WidgetBuilder builder;

  _SubNode(this.title, this.builder);
}
