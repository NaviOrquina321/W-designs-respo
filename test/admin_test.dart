import 'package:flutter_test/flutter_test.dart';
import 'package:doctor_appointment_app/services/admin_store.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  group('AdminStore Unit Tests', () {
    late AdminStore store;

    setUp(() {
      store = AdminStore();
    });

    test('Initial admin data sets are populated', () {
      expect(store.students.isNotEmpty, true);
      expect(store.matchings.isNotEmpty, true);
      expect(store.schedules.isNotEmpty, true);
      expect(store.payments.isNotEmpty, true);
      expect(store.notifications.isNotEmpty, true);
      expect(store.reports.isNotEmpty, true);
    });

    test('Validating a student updates status to validated', () {
      final unvalidatedStudent = store.students.firstWhere((s) => !s.isValidated);
      store.validateStudent(unvalidatedStudent.id);

      final updatedStudent = store.students.firstWhere((s) => s.id == unvalidatedStudent.id);
      expect(updatedStudent.isValidated, true);
    });

    test('Approving matching updates status to Approved', () {
      final matching = store.matchings.first;
      store.approveMatching(matching.id);

      final updated = store.matchings.firstWhere((m) => m.id == matching.id);
      expect(updated.status, 'Approved');
    });

    test('Cancelling matching updates status to Cancelled', () {
      final matching = store.matchings.first;
      store.cancelMatching(matching.id);

      final updated = store.matchings.firstWhere((m) => m.id == matching.id);
      expect(updated.status, 'Cancelled');
    });

    test('Adding schedule event expands schedule list', () {
      final initialLength = store.schedules.length;
      store.addSchedule(
        ScheduleSession(
          id: 'test_sch',
          title: 'Test Session',
          dateTime: 'Thu Oct 22, 10:00 AM',
          tutorName: 'Test Tutor',
          studentName: 'Test Student',
        ),
      );

      expect(store.schedules.length, initialLength + 1);
      expect(store.schedules.last.title, 'Test Session');
    });

    test('Confirming payment record updates status to Confirmed', () {
      final pendingPayment = store.payments.firstWhere((p) => p.status == 'Pending');
      store.confirmPayment(pendingPayment.id);

      final updated = store.payments.firstWhere((p) => p.id == pendingPayment.id);
      expect(updated.status, 'Confirmed');
    });

    test('Broadcasting notification adds new item to top of list', () {
      final initialLength = store.notifications.length;
      store.sendNotification(
        AdminNotification(
          id: 'test_notif',
          title: 'System Maintenance',
          message: 'Server upgrade tonight.',
          recipientGroup: 'All Users',
        ),
      );

      expect(store.notifications.length, initialLength + 1);
      expect(store.notifications.first.title, 'System Maintenance');
    });
  });

  group('Admin Dashboard Widget Tests', () {
    testWidgets('Renders Admin Dashboard modules layout', (WidgetTester tester) async {
      await tester.pumpWidget(const DoctorAppointmentApp());
      await tester.pumpAndSettle();

      // Navigate to Admin tab
      await tester.tap(find.text('Admin'));
      await tester.pumpAndSettle();

      expect(find.text('ADMIN MANAGEMENT PORTAL'), findsOneWidget);
      expect(find.text('Manage\nStudents'), findsOneWidget);
      expect(find.text('Manage Tutor\nMatching'), findsOneWidget);
      expect(find.text('Manage\nSchedule'), findsOneWidget);
      expect(find.text('Manage\nPayments'), findsOneWidget);
      expect(find.text('Manage\nNotifications'), findsOneWidget);
      expect(find.text('Manage\nReports'), findsOneWidget);
      expect(find.text('View Student\nList'), findsOneWidget);
    });
  });
}
