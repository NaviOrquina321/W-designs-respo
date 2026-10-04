import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../services/admin_store.dart';

class ManageScheduleScreen extends StatefulWidget {
  final int initialTab;

  const ManageScheduleScreen({super.key, this.initialTab = 0});

  @override
  State<ManageScheduleScreen> createState() => _ManageScheduleScreenState();
}

class _ManageScheduleScreenState extends State<ManageScheduleScreen> {
  final _titleController = TextEditingController();
  final _dateTimeController = TextEditingController();
  final _tutorController = TextEditingController();
  final _studentController = TextEditingController();

  @override
  void dispose() {
    _titleController.dispose();
    _dateTimeController.dispose();
    _tutorController.dispose();
    _studentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 2,
      initialIndex: widget.initialTab,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Manage Schedule'),
          backgroundColor: const Color(0xFF081C29),
          foregroundColor: Colors.white,
          bottom: const TabBar(
            indicatorColor: Colors.cyanAccent,
            labelColor: Colors.cyanAccent,
            unselectedLabelColor: Colors.white70,
            tabs: [
              Tab(text: 'New Calendar Schedule'),
              Tab(text: 'Modify Sessions Schedule'),
            ],
          ),
        ),
        body: Consumer<AdminStore>(
          builder: (context, store, child) {
            return TabBarView(
              children: [
                _buildNewScheduleForm(context, store),
                _buildModifyScheduleList(context, store),
              ],
            );
          },
        ),
      ),
    );
  }

  Widget _buildNewScheduleForm(BuildContext context, AdminStore store) {
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
                'Create New Schedule Event',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _titleController,
                decoration: const InputDecoration(
                  labelText: 'Session Title',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.event),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _dateTimeController,
                decoration: const InputDecoration(
                  labelText: 'Date & Time (e.g. Fri Oct 16, 03:00 PM)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.access_time),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _tutorController,
                decoration: const InputDecoration(
                  labelText: 'Tutor Name',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.person),
                ),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _studentController,
                decoration: const InputDecoration(
                  labelText: 'Student Name',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.school),
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
                        _dateTimeController.text.isNotEmpty) {
                      final newSession = ScheduleSession(
                        id: 'sch_${DateTime.now().millisecondsSinceEpoch}',
                        title: _titleController.text,
                        dateTime: _dateTimeController.text,
                        tutorName: _tutorController.text.isEmpty
                            ? 'Dr. Sarah Jenkins'
                            : _tutorController.text,
                        studentName: _studentController.text.isEmpty
                            ? 'Jordan Lee'
                            : _studentController.text,
                      );
                      store.addSchedule(newSession);

                      _titleController.clear();
                      _dateTimeController.clear();
                      _tutorController.clear();
                      _studentController.clear();

                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                            content: Text('New Schedule Event Created!')),
                      );
                    }
                  },
                  child: const Text('Add to Calendar Schedule',
                      style: TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildModifyScheduleList(BuildContext context, AdminStore store) {
    final schedules = store.schedules;

    if (schedules.isEmpty) {
      return const Center(child: Text('No scheduled sessions found.'));
    }

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: schedules.length,
      itemBuilder: (context, index) {
        final session = schedules[index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 12),
          child: ListTile(
            leading: const CircleAvatar(
              backgroundColor: Colors.blueAccent,
              child: Icon(Icons.calendar_month, color: Colors.white),
            ),
            title: Text(session.title,
                style: const TextStyle(fontWeight: FontWeight.bold)),
            subtitle: Text(
                '${session.dateTime}\nTutor: ${session.tutorName} • Student: ${session.studentName}'),
            isThreeLine: true,
            trailing: IconButton(
              icon: const Icon(Icons.edit, color: Colors.blueAccent),
              onPressed: () {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                      content: Text('Edit prompt for "${session.title}"')),
                );
              },
            ),
          ),
        );
      },
    );
  }
}
