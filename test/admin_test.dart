import 'package:flutter_test/flutter_test.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:doctor_appointment_app/services/admin_store.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  sqfliteFfiInit();
  databaseFactory = databaseFactoryFfi;

  group('AdminStore Unit Tests', () {
    late AdminStore store;

    setUp(() async {
      store = AdminStore();
      await store.initDatabase();
    });

    test('Initial admin data sets are populated', () {
      expect(store.students.isNotEmpty, true);
      expect(store.matchings.isNotEmpty, true);
      expect(store.schedules.isNotEmpty, true);
      expect(store.payments.isNotEmpty, true);
      expect(store.notifications.isNotEmpty, true);
      expect(store.reports.isNotEmpty, true);
    });

    test('Validating a student updates status to validated', () async {
      final unvalidatedStudent = store.students.firstWhere((s) => !s.isValidated, orElse: () => store.students.first);
      await store.validateStudent(unvalidatedStudent.id);

      final updatedStudent = store.students.firstWhere((s) => s.id == unvalidatedStudent.id);
      expect(updatedStudent.isValidated, true);
    });

    test('Approving matching updates status to Approved', () async {
      final matching = store.matchings.first;
      await store.approveMatching(matching.id);

      final updated = store.matchings.firstWhere((m) => m.id == matching.id);
      expect(updated.status, 'Approved');
    });

    test('Cancelling matching updates status to Cancelled', () async {
      final matching = store.matchings.first;
      await store.cancelMatching(matching.id);

      final updated = store.matchings.firstWhere((m) => m.id == matching.id);
      expect(updated.status, 'Cancelled');
    });

    test('Adding schedule event expands schedule list', () async {
      final initialLength = store.schedules.length;
      await store.addSchedule(
        ScheduleSession(
          id: 'test_sch_${DateTime.now().millisecondsSinceEpoch}',
          title: 'Test Session',
          dateTime: 'Thu Oct 22, 10:00 AM',
          tutorName: 'Test Tutor',
          studentName: 'Test Student',
        ),
      );

      expect(store.schedules.length, initialLength + 1);
      expect(store.schedules.last.title, 'Test Session');
    });

    test('Confirming payment record updates status to Confirmed', () async {
      final payment = store.payments.first;
      await store.confirmPayment(payment.id);

      final updated = store.payments.firstWhere((p) => p.id == payment.id);
      expect(updated.status, 'Confirmed');
    });

    test('Broadcasting notification adds new item to top of list', () async {
      final initialLength = store.notifications.length;
      await store.sendNotification(
        AdminNotification(
          id: 'test_notif_${DateTime.now().millisecondsSinceEpoch}',
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
