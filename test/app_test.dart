import 'package:flutter_test/flutter_test.dart';
import 'package:doctor_appointment_app/models/appointment.dart';
import 'package:doctor_appointment_app/services/appointment_store.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  group('AppointmentStore Unit Tests', () {
    late AppointmentStore store;

    setUp(() {
      store = AppointmentStore();
    });

    test('Initial doctors and categories load correctly', () {
      expect(store.doctors.isNotEmpty, true);
      expect(store.categories.isNotEmpty, true);
      expect(store.upcomingAppointments.length, 1);
    });

    test('Filtering doctors by search query works', () {
      store.setSearchQuery('Jenkins');
      expect(store.filteredDoctors.length, 1);
      expect(store.filteredDoctors.first.name, 'Dr. Sarah Jenkins');

      store.setSearchQuery('NonExistentDoctor');
      expect(store.filteredDoctors.isEmpty, true);
    });

    test('Filtering doctors by category works', () {
      store.setSelectedCategory('cardiology');
      expect(store.filteredDoctors.length, 1);
      expect(store.filteredDoctors.first.categoryId, 'cardiology');

      // Toggling category off
      store.setSelectedCategory('cardiology');
      expect(store.filteredDoctors.length, store.doctors.length);
    });

    test('Booking an appointment adds to upcoming appointments list', () {
      final initialCount = store.upcomingAppointments.length;
      final doctor = store.doctors.first;

      final apt = store.bookAppointment(
        doctor: doctor,
        date: 'Friday, 10:00 AM',
        timeSlot: '10:00 AM',
        patientName: 'Jane Doe',
        patientPhone: '+1 555-9999',
        patientReason: 'Checkup',
      );

      expect(store.upcomingAppointments.length, initialCount + 1);
      expect(store.upcomingAppointments.first.id, apt.id);
      expect(apt.patientName, 'Jane Doe');
      expect(apt.status, AppointmentStatus.upcoming);
    });

    test('Cancelling an appointment updates status to cancelled', () {
      final aptId = store.upcomingAppointments.first.id;
      store.cancelAppointment(aptId);

      expect(store.upcomingAppointments.isEmpty, true);
      expect(store.cancelledAppointments.length, 1);
      expect(store.cancelledAppointments.first.id, aptId);
    });

    test('Toggling favorite status works', () {
      final doctor = store.doctors.first;
      final initialFav = doctor.isFavorite;

      store.toggleFavorite(doctor.id);
      expect(store.doctors.first.isFavorite, !initialFav);
    });
  });

  group('Doctor Appointment App Widget Tests', () {
    testWidgets('Home screen renders doctors list and header', (WidgetTester tester) async {
      await tester.pumpWidget(const DoctorAppointmentApp());
      await tester.pumpAndSettle();

      expect(find.text('Hello, Alex 👋'), findsOneWidget);
      expect(find.text('Specialties'), findsOneWidget);
      expect(find.text('Top Doctors'), findsOneWidget);
      expect(find.text('Dr. Sarah Jenkins'), findsWidgets);
    });

    testWidgets('Navigation bar switches tabs', (WidgetTester tester) async {
      await tester.pumpWidget(const DoctorAppointmentApp());
      await tester.pumpAndSettle();

      // Tap on Explore tab item in BottomNavigationBar
      await tester.tap(find.text('Explore'));
      await tester.pumpAndSettle();

      expect(find.text('Find Doctors'), findsOneWidget);

      // Tap on Appointments tab item in BottomNavigationBar
      await tester.tap(find.text('Appointments'));
      await tester.pumpAndSettle();

      expect(find.text('My Appointments'), findsOneWidget);
    });
  });
}
