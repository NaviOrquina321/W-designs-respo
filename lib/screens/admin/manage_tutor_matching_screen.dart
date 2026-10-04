import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManageTutorMatchingScreen extends StatelessWidget {
  final int initialTab;

  const ManageTutorMatchingScreen({super.key, this.initialTab = 0});

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      initialIndex: initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Tutor Matching'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'Review Results'),
              Tab(text: 'Approve Sessions'),
              Tab(text: 'Cancel Sessions'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildMatchingList(context, store, filterStatus: null),
                _buildMatchingList(context, store, filterStatus: 'Pending', isApprovalMode: true),
                _buildMatchingList(context, store, filterStatus: 'Approved', isCancelMode: true),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildMatchingList(
    BuildContext context,
    AdminStore store, {
    String? filterStatus,
    bool isApprovalMode = false,
    bool isCancelMode = false,
  }) {
    final matchings = filterStatus == null
        ? store.matchings
        : store.matchings.where((m) => m.status == filterStatus).toList();

    if (matchings.isEmpty) {
      return const Center(
        child: Text('No matching sessions found for this view.',
            style: TextStyle(color: Colors.grey)),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: matchings.length,
      itemBuilder: (context, index) {
        final item = matchings[index];
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
                      item.subject,
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
                        color: Colors.teal.withAlpha(30),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        item.matchScore,
                        style: const TextStyle(
                          color: Colors.teal,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text('Student: ${item.studentName}',
                    style: const TextStyle(fontWeight: FontWeight.w500)),
                Text('Tutor: ${item.tutorName}',
                    style: const TextStyle(fontWeight: FontWeight.w500)),
                const SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'Status: ${item.status}',
                      style: TextStyle(
                        color: item.status == 'Approved'
                            ? Colors.green
                            : item.status == 'Cancelled'
                                ? Colors.red
                                : Colors.orange,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (isApprovalMode)
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.green,
                          foregroundColor: Colors.white,
                        ),
                        onPressed: () {
                          store.approveMatching(item.id);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Session Approved!')),
                          );
                        },
                        child: const Text('Approve'),
                      ),
                    if (isCancelMode)
                      OutlinedButton(
                        style: OutlinedButton.styleFrom(
                          foregroundColor: Colors.red,
                          side: const BorderSide(color: Colors.red),
                        ),
                        onPressed: () {
                          store.cancelMatching(item.id);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(content: Text('Session Cancelled!')),
                          );
                        },
                        child: const Text('Cancel Session'),
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
