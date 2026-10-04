import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManageNotificationsScreen extends StatefulWidget {
  final int initialTab;

  const ManageNotificationsScreen({super.key, this.initialTab = 0});

  @override
  State<ManageNotificationsScreen> createState() =>
      _ManageNotificationsScreenState();
}

class _ManageNotificationsScreenState
    extends State<ManageNotificationsScreen> {
  final _titleController = TextEditingController();
  final _messageController = TextEditingController();
  String _selectedRecipientGroup = 'All Students';

  final List<String> _recipientGroups = [
    'All Students',
    'All Tutors',
    'All Users',
  ];

  @override
  void dispose() {
    _titleController.dispose();
    _messageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      initialIndex: widget.initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Notifications'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'Send Notifications'),
              Tab(text: 'View Notifications'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildSendNotificationForm(context, store),
                _buildViewNotificationsList(context, store),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildSendNotificationForm(
      BuildContext context, AdminStore store) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Card(
        elevation: 2,
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Broadcast Announcement',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _selectedRecipientGroup,
                decoration: const InputDecoration(
                  labelText: 'Recipient Group',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.group),
                ),
                items: _recipientGroups
                    .map((group) => DropdownMenuItem(
                          value: group,
                          child: Text(group),
                        ))
                    .toList(),
                onChanged: (val) {
                  if (val != null) {
                    setState(() => _selectedRecipientGroup = val);
                  }
                },
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Notification Title',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.title),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _messageController,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Notification Message Body',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.message),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                height: 48,
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blueAccent,
                    foregroundColor: Colors.white,
                  ),
                  onPressed: () {
                    if (_titleController.text.isNotEmpty &&
                        _messageController.text.isNotEmpty) {
                      final notif = AdminNotification(
                        id: 'notif_${DateTime.now().millisecondsSinceEpoch}',
                        title: _titleController.text,
                        message: _messageController.text,
                        recipientGroup: _selectedRecipientGroup,
                      );
                      store.sendNotification(notif);

                      _titleController.clear();
                      _messageController.clear();

                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text('Notification Broadcast Sent!')),
                      );
                    }
                  },
                  child: const Text('Send Broadcast Notification',
                      style: TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildViewNotificationsList(
      BuildContext context, AdminStore store) {
    final notifications = store.notifications;

    if (notifications.isEmpty) {
      return const Center(
          child: Text('No notifications sent yet.',
              style: TextStyle(color: Colors.grey)));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: notifications.length,
      itemBuilder: (context, index) {
        final notif = notifications[index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: const CircleAvatar(
              backgroundColor: Colors.blueAccent,
              child: Icon(Icons.notifications_active, color: Colors.white),
            ),
            title: Text(notif.title,
                style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text(
                'To: ${notif.recipientGroup}\n${notif.message}\n${notif.timestamp.toString().split('.').first}'),
            isThreeLine: true,
          ),
        );
      },
    );
  }
}
