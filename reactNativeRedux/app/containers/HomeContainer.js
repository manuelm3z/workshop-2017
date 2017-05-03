import React, {
	Component
} from 'react';
import {
	ScrollView,
	View,
	Text,
	TextInput,
	Image,
	TouchableHighlight,
	StyleSheet
} from 'react-native';
import {
	connect
} from 'react-redux';

class HomeContainer extends Component {
	searchPressed() {
		this.props.fetchRecipes('bacon,cucumber,banana');
	}

	recipes() {
		return Object.keys(this.props.searchedRecipes).map(key => {
			this.props.searchedRecipes[key];
		});
	}

	render() {
		return (
			<View style={styles.scene}>
				<View style={styles.searchSection}>
					<TouchableHighlight onPress={() => this.searchPressed()}>
						<Text>Fetch Recipes</Text>
					</TouchableHighlight>
				</View>
				<ScrollView style={styles.scrollSection}>{this.recipes().map(recipe => {
					return (
						<View key={recipe.id}>
							<Image source={{uri: recipe.image}} style={styles.resultImage}/>
							<Text style={styles.resultText}>{recipe.title}</Text>
						</View>
					);
				})}</ScrollView>
			</View>
		);
	}
}

const styles = StyleSheet.create({
	scene: {
		flex: 1,
		marginTop: 20
	},
	searchSection: {
		height: 30,
		borderBottomColor: '#000',
		borderBottomWidth: 1,
		padding: 5
	},
	scrollSection: {
		flex: 0.8
	},
	resultImage: {
		height: 150
	},
	resultText: {
		backgroundColor: '#000',
		color: '#FFF',
		height: 20
	}
});

function mapStateToProps(state) {
	return {
		searchedRecipes: state.searchedRecipes
	};
}

export default connect(mapStateToProps)(HomeContainer);