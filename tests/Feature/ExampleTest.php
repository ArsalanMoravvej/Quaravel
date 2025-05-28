<?php


describe('Unauthenticated API Access', function () {

    test('guests cannot access todos index', function () {
        $response = $this->getJson('/api/v1/surveys');
        $response->assertStatus(200);
    });

//    test('guests cannot create todos', function () {
//        $todoData = [
//            'title' => 'New Todo',
//            'description' => 'Todo description'
//        ];
//
//        $response = $this->postJson('/api/v1/todos', $todoData);
//        $response->assertStatus(401);
//    });
//
//    test('guests cannot view specific todo', function () {
//        $todo = Todo::factory()->create();
//
//        $response = $this->getJson("/api/v1/todos/{$todo->id}");
//        $response->assertStatus(401);
//    });
//
//    test('guests cannot update todo', function () {
//        $todo = Todo::factory()->create();
//
//        $response = $this->putJson("/api/v1/todos/{$todo->id}", [
//            'title' => 'Updated Title'
//        ]);
//        $response->assertStatus(401);
//    });
//
//    test('guests cannot delete todo', function () {
//        $todo = Todo::factory()->create();
//
//        $response = $this->deleteJson("/api/v1/todos/{$todo->id}");
//        $response->assertStatus(401);
//    });
});

