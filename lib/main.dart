import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'services/appointment_store.dart';
import 'screens/main_navigation_screen.dart';

void main() {
  runApp(const DoctorAppointmentApp());
}

class DoctorAppointmentApp extends StatelessWidget {
  const DoctorAppointmentApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ChangeNotifierProvider(
      create: (_) => AppointmentStore(),
      child: MaterialApp(
        title: 'Doctor Appointment App',
        debugShowCheckedModeBanner: false,
        theme: ThemeData(
          primarySwatch: Colors.blue,
          primaryColor: Colors.blueAccent,
          useMaterial3: true,
          scaffoldBackgroundColor: const Color(0xFFF8F9FA),
          appBarTheme: const AppBarTheme(
            backgroundColor: Colors.transparent,
            elevation: 0,
            iconTheme: IconThemeData(color: Colors.black),
            titleTextStyle: TextStyle(
              color: Colors.black,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        home: const MainNavigationScreen(),
      ),
    );
  }
}
