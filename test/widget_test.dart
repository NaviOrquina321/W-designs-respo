import 'package:flutter_test/flutter_test.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  testWidgets('Doctor appointment app smoke test', (WidgetTester tester) async {
    await tester.pumpWidget(const DoctorAppointmentApp());
    expect(find.text('CarePulse - Doctor Appointments'), findsNothing); // Title is in MaterialApp
    expect(find.text('Categories'), findsOneWidget);
    expect(find.text('Top Doctors'), findsOneWidget);
  });
}
