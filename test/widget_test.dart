import 'package:flutter_test/flutter_test.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';
import 'package:doctor_appointment_app/main.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  sqfliteFfiInit();
  databaseFactory = databaseFactoryFfi;

  testWidgets('App initialization test', (WidgetTester tester) async {
    await tester.pumpWidget(const DoctorAppointmentApp());
    await tester.pumpAndSettle();
    expect(find.text('Hello, Alex 👋'), findsOneWidget);
  });
}
