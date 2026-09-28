import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManageReportsScreen extends StatelessWidget {
  final int initialTab;

  const ManageReportsScreen({super.key, this.initialTab = 0});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      initialIndex: initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Reports'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'Completed Tutoring'),
              Tab(text: 'Weekly Sessions'),
              Tab(text: 'Monthly Sessions'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildReportList(context, store, 'Completed'),
                _buildReportList(context, store, 'Weekly'),
                _buildReportList(context, store, 'Monthly'),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildReportList(
      BuildContext context, AdminStore store, String filterPeriod) {
    final reports =
        store.reports.where((r) => r.period == filterPeriod).toList();

    if (reports.isEmpty) {
      return Center(
        child: Text('No $filterPeriod tutoring reports found.',
            style: const TextStyle(color: Colors.grey)),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: reports.length,
      itemBuilder: (context, index) {
        final rep = reports[index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      rep.subject,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Colors.blueAccent,
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.blueAccent.withAlpha(30),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '${rep.totalHours} Hours',
                        style: const TextStyle(
                          color: Colors.blueAccent,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text('Tutor: ${rep.tutorName}',
                    style: const TextStyle(fontWeight: FontWeight.w500)),
                Text('Student: ${rep.studentName}',
                    style: const TextStyle(fontWeight: FontWeight.w500)),
                const SizedBox(height: 8),
                Row(
                  children: [
                    const Icon(Icons.bar_chart, size: 16, color: Colors.grey),
                    const SizedBox(width: 4),
                    Text(
                      'Period Category: ${rep.period}',
                      style: const TextStyle(color: Colors.grey, fontSize: 13),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
