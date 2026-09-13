import 'package:flutter_test/flutter_test.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  testWidgets('App initialization test', (WidgetTester tester) async {
    await tester.pumpWidget(const DoctorAppointmentApp());
    expect(find.text('Hello, Alex 👋'), findsOneWidget);
  });
}
